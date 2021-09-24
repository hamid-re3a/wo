<?php

namespace Orders\Http\Controllers\Front;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MLM\Services\Grpc\Acknowledge;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Http\Requests\Front\Order\ShowRequest;
use Orders\Http\Resources\OrderResource;
use Orders\Models\Order;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use Payments\Services\Grpc\Invoice;
use Payments\Services\PaymentDrivers;
use Payments\Services\PaymentService;
use Payments\Services\Processors\PaymentProcessor;
use User\Models\User;

class OrderController extends Controller
{
    use  ValidatesRequests;

    private $payment_processor;
    private $package_service;

    public function __construct(PaymentProcessor $paymentProcessor, PackageService $package_service)
    {
        $this->payment_processor = $paymentProcessor;
        $this->package_service = $package_service;
    }

    /**
     * Order Counts
     * @group
     * Public User > Orders
     */
    public function counts()
    {
        $total_counts = User::query()->whereId(auth()->user()->id)->withCount([
            'orders AS total_orders',
            'orders AS total_paid' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
            },
            'orders AS total_resolved' => function (Builder $query) {
                $query->whereNotNull('is_resolved_at');
            },
            'orders AS total_refund' => function (Builder $query) {
                $query->whereNotNull('is_refund_at');
            },
            'orders AS total_commission_paid' => function (Builder $query) {
                $query->whereNotNull('is_commission_resolved_at');
            },
            'orders AS total_paid_with_wallet' => function (Builder $query) {
                $query->where('payment_driver', 'deposit');
            },
            'orders AS total_paid_with_giftcode' => function (Builder $query) {
                $query->where('payment_driver', 'giftcode');
            },
            'orders AS total_paid_with_purchase' => function (Builder $query) {
                $query->where('payment_driver', 'purchase');
            },
            'orders AS total_plan_start' => function (Builder $query) {
                $query->where('plan', ORDER_PLAN_START);
            },
            'orders AS total_plan_purchase' => function (Builder $query) {
                $query->where('plan', ORDER_PLAN_PURCHASE);
            }
        ])->first()->toArray();

        return api()->success(null, [
            'total_orders' => $total_counts['total_orders'],
            'total_paid' => $total_counts['total_paid'],
            'total_resolved' => $total_counts['total_resolved'],
            'total_commission_paid' => $total_counts['total_commission_paid'],
            'total_paid_with_wallet' => $total_counts['total_paid_with_wallet'],
            'total_paid_with_giftcode' => $total_counts['total_paid_with_giftcode'],
            'total_paid_with_purchase' => $total_counts['total_paid_with_purchase'],
            'total_plan_start' => $total_counts['total_plan_start'],
            'total_plan_purchase' => $total_counts['total_plan_purchase']
        ]);
    }

    /**
     * List orders
     * @group
     * Public User > Orders
     * @param ListOrderRequest $request
     * @return JsonResponse
     */
    public function index(ListOrderRequest $request)
    {
        $orders = auth()->user()->orders()->filter()->simplePaginate();
        return api()->success(null, OrderResource::collection($orders)->response()->getData());
    }

    /**
     * Get order details
     * @group
     * Public User > Orders
     * @param ShowRequest $request
     * @return JsonResponse
     */
    public function showOrder(ShowRequest $request)
    {
        $order = auth()->user()->orders()->find($request->get('id'))->first();
        return api()->success(null, OrderResource::make($order));
    }

    /**
     * Submit new Order
     * @group
     * Public User > Orders
     * @param OrderRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function newOrder(OrderRequest $request)
    {
        try {
            $package = $this->validatePackage($request);
            /**@var $user User*/
            $user = auth()->user();

            DB::beginTransaction();
            $order_db = Order::query()->create([
                "user_id" => $request->header('X-user-id'),
                "payment_type" => $request->get('payment_type'),
                "payment_currency" => $request->has('payment_currency') ? $request->get('payment_currency') : null,
                "payment_driver" => $request->has('payment_driver') ? $request->get('payment_driver') : null,
                "package_id" => $request->get('package_id'),
                'validity_in_days' => $package->getValidityInDays(),
                'plan' => $user->paidOrders()->exists() ? ORDER_PLAN_PURCHASE : ORDER_PLAN_START
            ]);
            $order_db->refreshOrder();
            //Order Resolver
            /** @var $response Acknowledge */
            list($response, $flag) = getMLMGrpcClient()->simulateOrder($order_db->getOrderService())->wait();

            if ($flag->code != 0)
                throw new \Exception('MLM not responding 1', 406);


            if (!$response->getStatus()) {
                throw new \Exception($response->getMessage(), 406);
            }

            //Invoice service
            $invoice_request = new Invoice();
            $invoice_request->setPayableId((int)$order_db->id);
            $invoice_request->setPayableType('Order');
            $invoice_request->setPfAmount($order_db->total_cost_in_usd);
            $invoice_request->setPaymentDriver($order_db->payment_driver);
            $invoice_request->setPaymentType($order_db->payment_type);
            $invoice_request->setPaymentCurrency($order_db->payment_currency);
            $invoice_request->setUser($user->getUserService());
            $invoice_request->setUserId(auth()->user()->id);
            $invoice_request->setPayableId($order_db->id);
            $invoice_request->setPayableType('Order');

            list($payment_flag, $payment_response) = $this->payment_processor->pay($invoice_request);


            if (!$payment_flag)
                throw new \Exception($payment_response, 406);

            if ($request->get('payment_type') != 'purchase') {


                /** @var $submit_response Acknowledge */
                list($submit_response, $flag) = getMLMGrpcClient()->submitOrder($order_db->getOrderService())->wait();
                if ($flag->code != 0)
                    throw new \Exception('MLM not responding 2', 406);

                $order_db->update([
                    'is_resolved_at' => $submit_response->getCreatedAt()
                ]);
            }

            DB::commit();
            return api()->success(null, $payment_response);

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('OrderController@newOrder => ' . $exception->getMessage());
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ], $exception->getCode(), null);
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
