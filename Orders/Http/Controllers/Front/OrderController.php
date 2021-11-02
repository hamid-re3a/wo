<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Models\Order;
use Orders\Services\MlmClientFacade;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use Payments\Services\Grpc\Invoice;
use Payments\Services\Processors\PaymentFacade;
use User\Models\User;

class OrderController extends Controller
{
    use  ValidatesRequests;

    private $package_service;

    public function __construct(PackageService $package_service)
    {
        $this->package_service = $package_service;
    }

    /**
     * Submit new Order
     * @group
     * Public User > Orders
     * @param OrderRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function newOrder(OrderRequest $request)
    {
        try {
            $package = $this->validatePackage($request);

            /**@var $user User */
            $user = auth()->user();

            $user_who_pays_for_package = $user;
            $user_who_is_package_for = $user;
            if($request->has('to_user_id')){
                $user_who_is_package_for = User::query()->findOrFail($request->get('to_user_id'));
            }



            DB::beginTransaction();
            $now = now()->toDateTimeString();
            $order_db = Order::query()->create([
                "from_user_id" => $user_who_pays_for_package->id,
                "user_id" => $user_who_is_package_for->id,
                "payment_type" => $request->get('payment_type'),
                "payment_currency" => $request->get('payment_type') == 'purchase' ? $request->get('payment_currency') : null,
                "payment_driver" => $request->get('payment_type') == 'purchase' ? $request->get('payment_driver') : null,
                "package_id" => $request->get('package_id'),
                "is_paid_at" => $now,
                "is_resolved_at" => $now,
                'validity_in_days' => $package->getValidityInDays(),
                'plan' => $user_who_is_package_for->paidOrders()->exists() ? ORDER_PLAN_PURCHASE : ORDER_PLAN_START
            ]);
            $order_db->refreshOrder();

            Log::info('Front/OrderController@newOrder First MLM request');
            $response = MlmClientFacade::simulateOrder($order_db->getGrpcMessage());

            if (!$response->getStatus()) {
                throw new \Exception($response->getMessage(), 406);
            }

            //Invoice service
            $invoice_request = new Invoice();
            $invoice_request->setPayableId((int)$order_db->id);
            $invoice_request->setPayableType('Order');
            $invoice_request->setPfAmount((double)$order_db->total_cost_in_pf);
            $invoice_request->setPaymentDriver($order_db->payment_driver);
            $invoice_request->setPaymentType($order_db->payment_type);
            $invoice_request->setPaymentCurrency($order_db->payment_currency);
            $invoice_request->setUser($user_who_pays_for_package->getGrpcMessage());
            $invoice_request->setUserId((int)auth()->user()->id);

            list($payment_flag, $payment_response) = PaymentFacade::pay($invoice_request, $order_db->getGrpcMessage());


            if (!$payment_flag)
                throw new \Exception($payment_response, 406);

            if ($request->get('payment_type') != 'purchase') {

                $order_service = $order_db->fresh()->getGrpcMessage();

                Log::info('Front/OrderController@newOrder Second MLM request');
                $submit_response = MlmClientFacade::submitOrder($order_service);

                if (!$response->getStatus()) {
                    throw new \Exception($response->getMessage(), 406);
                }

                $order_db->update([
                    'is_paid_at' => $now,
                    'is_resolved_at' => $now,
                    'is_commission_resolved_at' => $submit_response->getCreatedAt()
                ]);
            }

            DB::commit();
            return api()->success(null, $payment_response);

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('OrderController@newOrder => ' . $exception->getMessage());
            throw $exception;
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
