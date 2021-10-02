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
            DB::beginTransaction();
            $order_db = Order::query()->create([
                "user_id" => $user->id,
                "payment_type" => $request->get('payment_type'),
                "payment_currency" => $request->has('payment_currency') AND $request->get('payment_type') != 'deposit' ? $request->get('payment_currency') : null,
                "payment_driver" => $request->has('payment_driver') AND $request->get('payment_type') != 'deposit' ? $request->get('payment_driver') : null,
                "package_id" => $request->get('package_id'),
                'validity_in_days' => $package->getValidityInDays(),
                'plan' => $user->paidOrders()->exists() ? ORDER_PLAN_PURCHASE : ORDER_PLAN_START
            ]);
            $order_db->refreshOrder();

            $response = MlmClientFacade::simulateOrder($order_db->getOrderService());

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
            $invoice_request->setUser($user->getUserService());
            $invoice_request->setUserId((int)auth()->user()->id);

            list($payment_flag, $payment_response) = PaymentFacade::pay($invoice_request, $order_db->getOrderService());


            if (!$payment_flag)
                throw new \Exception($payment_response, 406);

            if ($request->get('payment_type') != 'purchase') {

                $now = now()->toDateTimeString();

                $order_service = $order_db->fresh()->getOrderService();
                $order_service->setIsPaidAt($now);
                $order_service->setIsResolvedAt($now);

                Log::info('Second MLM request');
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
