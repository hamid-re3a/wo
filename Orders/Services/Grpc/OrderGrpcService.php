<?php


namespace Orders\Services\Grpc;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mix\Grpc\Context;
use Orders\Models\Order as OrderModel;
use Orders\Services\MlmClientFacade;
use Packages\Services\Grpc\Id;
use Packages\Services\Grpc\Package;
use Packages\Services\PackageService;
use Payments\Services\Grpc\Invoice;
use Payments\Services\Processors\PaymentFacade;
use User\Services\UserService;

class OrderGrpcService implements OrdersServiceInterface
{

    /**
     * @var $user_service UserService
     */
    private $user_service;

    public function __construct()
    {
        $this->user_service = app(UserService::class);
    }

    /**
     * @inheritDoc
     */
    public function sponsorPackage(Context $context, Order $order): Acknowledge
    {
        $acknowledge = new Acknowledge();
        try {
            $package = $this->validatePackage($order->getPackageId());


            $user_who_pays_for_package = $this->user_service->findByIdOrFail($order->getFromUserId());
            $user_who_is_package_for = $this->user_service->findByIdOrFail($order->getUserId());

            if(is_null($user_who_pays_for_package) || is_null($user_who_is_package_for) )
                throw new \Exception('User not found', 406);

            DB::beginTransaction();
            $order_db = OrderModel::query()->create([
                "from_user_id" => $user_who_pays_for_package->id,
                "user_id" => $user_who_is_package_for->id,
                "payment_type" => 'deposit',
                "package_id" => $order->getPackageId(),
                'validity_in_days' => $package->getValidityInDays(),
                'plan' => $user_who_is_package_for->paidOrders()->exists() ? ORDER_PLAN_PURCHASE : ORDER_PLAN_START
            ]);
            if(is_null($order_db) )
                throw new \Exception('Creating Order faced a problem', 406);
            $order_db->refreshOrder();
            Log::info('Front/OrderService@newOrder First MLM request');
            list($response, $flag) = getMLMGrpcClient()->simulateOrder($order_db->getOrderService())->wait();
            if ($flag->code != 0)
                throw new \Exception('Order not see mlm', 406);

            if (!$response->getStatus()) {
                throw new \Exception($response->getMessage(), 406);
            }

            //Invoice service
            $invoice_request = new Invoice();
            $invoice_request->setPayableId((int)$order_db->id);
            $invoice_request->setPayableType('Order');
            $invoice_request->setPfAmount((double)$order_db->total_cost_in_pf);
            $invoice_request->setPaymentType($order_db->payment_type);
            $invoice_request->setUser($user_who_pays_for_package->getUserService());
            $invoice_request->setUserId((int)$user_who_pays_for_package->id);

            list($payment_flag, $payment_response) = PaymentFacade::pay($invoice_request, $order_db->getOrderService());


            if (!$payment_flag)
                throw new \Exception($payment_response, 406);


            $now = now()->toDateTimeString();

            $order_service = $order_db->fresh()->getOrderService();
            $order_service->setIsPaidAt($now);
            $order_service->setIsResolvedAt($now);

            Log::info('Front/OrderService@newOrder Second MLM request');
            list($submit_response, $flag) = getMLMGrpcClient()->simulateOrder($order_service)->wait();
            if ($flag->code != 0)
                throw new \Exception('Order not see1 mlm', 406);

            if (!$response->getStatus()) {
                throw new \Exception($response->getMessage(), 406);
            }

            $order_db->update([
                'is_paid_at' => $now,
                'is_resolved_at' => $now,
                'is_commission_resolved_at' => $submit_response->getCreatedAt()
            ]);

            DB::commit();
            $acknowledge->setStatus(true);
            return $acknowledge;

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('OrderService@newOrder => ' . $exception->getMessage());
            $acknowledge->setStatus(false);
            $acknowledge->setMessage($exception->getMessage());
            return $acknowledge;
        }
    }


    private function validatePackage($package_id): Package
    {

        $id = new Id;
        $id->setId($package_id);
        $package = app(PackageService::class)->packageFullById($id);
        if (!$package->getId()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'package_ids' => ['Package does not exist'],
            ]);
        }

        return $package;
    }

}
