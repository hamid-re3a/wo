<?php

namespace Orders\Http\Controllers\Admin;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orders\Http\Requests\Admin\Order\OrderRequest;
use Orders\Http\Resources\Admin\OrderResource;
use Orders\Models\Order;
use Orders\Services\MlmClientFacade;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use Payments\Services\PaymentService;
use User\Models\User;

class OrderController extends Controller
{
    //TODO Refactor whole controller :|
    use  ValidatesRequests;

    private $payment_service;
    private $package_service;

    public function __construct(PaymentService $payment_service, PackageService $package_service)
    {
        $this->payment_service = $payment_service;
        $this->package_service = $package_service;
    }

    /**
     * Get orders
     * @group
     * Admin User > Orders Dashboard
     */
    public function index()
    {
        $list = Order::query()->orderByDesc('id')->filter()->with('user')->paginate();
        return api()->success(null,[
            'list' => OrderResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage()
            ]
        ]);
    }

    /**
     * Submit new Order
     * @group
     * Admin User > Orders
     * @param OrderRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function newOrder(OrderRequest $request)
    {
        try {
            $package = $this->validatePackage($request);
            /**@var $user User */
            $user = auth()->user();

            DB::beginTransaction();
            $order_db = Order::query()->create([
                "from_user_id" => $user->id,
                "user_id" => $request->get('user_id'),
                "payment_type" => 'admin',
                "package_id" => $request->get('package_id'),
                'validity_in_days' => $package->getValidityInDays(),
                'plan' => $request->get('plan')
            ]);
            $order_db->refreshOrder();

            $response = MlmClientFacade::simulateOrder($order_db->getOrderService());

            if (!$response->getStatus()) {
                throw new \Exception($response->getMessage(), 406);
            }

            $now = now()->toDateTimeString();

            $order_service = $order_db->fresh()->getOrderService();
            $order_service->setIsPaidAt($now);
            $order_service->setIsResolvedAt($now);

            $submit_response = MlmClientFacade::submitOrder($order_db->getOrderService());
            $order_db->update([
                'is_paid_at' => $now,
                'is_resolved_at' => $now,
                'is_commission_resolved_at' => $submit_response->getCreatedAt()
            ]);


        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('OrderController@newOrder => ' . $exception->getMessage());
            throw new $exception;
        }
    }

    private function validatePackage(Request $request)
    {

        $id = new Id;
        $id->setId($request->package_id);
        $package = $this->package_service->packageFullById($id);
        if (!$package->getId()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'package_ids' => ['Package does not exist'],
            ]);
        }

        return $package;
    }

}
