<?php

namespace Orders\Http\Controllers\Admin;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MLM\Services\Grpc\Acknowledge;
use Orders\Http\Requests\Admin\Order\OrderRequest;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderTypeFilterRequest;
use Orders\Http\Resources\CountDataResource;
use Orders\Models\Order;
use Orders\Services\MlmClientFacade;
use Orders\Services\OrderService;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use Payments\Services\Grpc\Invoice;
use Payments\Services\PaymentService;
use User\Models\User;

class OrderController extends Controller
{
    use  ValidatesRequests;

    private $payment_service;
    private $package_service;
    private $order_service;

    public function __construct(PaymentService $payment_service, PackageService $package_service, OrderService $order_service)
    {
        $this->payment_service = $payment_service;
        $this->package_service = $package_service;
        $this->order_service = $order_service;
    }

    /**
     * get count subscriptions
     * @group
     * Admin User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function getCountSubscriptions()
    {
        return api()->success(null, new CountDataResource($this->order_service->getCountPackageSubscriptions()));
    }

    /**
     * get count active package
     * @group
     * Admin User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function activePackageCount()
    {
        return api()->success(null, new CountDataResource($this->order_service->activePackageCount()));
    }

    /**
     * get count deactivate package
     * @group
     * Admin User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function deactivatePackageCount()
    {
        return api()->success(null, new CountDataResource($this->order_service->deactivatePackageCount()));
    }

    /**
     * get package overview count
     * @group
     * Admin User > Orders
     * @param OrderTypeFilterRequest $request
     * @return JsonResponse
     */
    public function packageOverviewCount(OrderTypeFilterRequest $request)
    {
        return api()->success(null, $this->order_service->packageOverviewCount($request->type));
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
