<?php

namespace Orders\Http\Controllers\Front;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orders\Http\Requests\Front\Order\ListOrderRequest;
use Orders\Http\Requests\Front\Order\OrderRequest;
use Orders\Http\Requests\Front\Order\ShowRequest;
use Orders\Http\Resources\OrderResource;
use Orders\Models\Order;
use Orders\Services\Grpc\MlmClientFacade;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use Payments\Services\Grpc\Invoice;
use Payments\Services\PaymentDrivers;
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
     * Order Counts
     * @group
     * Public User > Orders
     */
    public function counts()
    {
        $now = now()->toDateTimeString();
        $total_counts = User::query()->whereId(auth()->user()->id)->withCount([
            'orders AS total_orders',
            'orders AS total_paid' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
            },
            'orders AS total_resolved' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->whereNotNull('is_resolved_at');
            },
            'orders AS total_refund' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->whereNotNull('is_refund_at');
            },
            'orders AS total_commission_paid' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->whereNotNull('is_commission_resolved_at');
            },
            'orders AS total_paid_with_wallet' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->where('payment_driver', 'deposit');
            },
            'orders AS total_paid_with_giftcode' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->where('payment_driver', 'giftcode');
            },
            'orders AS total_paid_with_purchase' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->where('payment_driver', 'purchase');
            },
            'orders AS total_plan_start' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->where('plan', ORDER_PLAN_START);
            },
            'orders AS total_plan_purchase' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->where('plan', ORDER_PLAN_PURCHASE);
            },
            'orders AS total_plan_special' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->where('plan', ORDER_PLAN_SPECIAL);
            },
            'orders AS total_plan_company' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
                $query->where('plan', ORDER_PLAN_COMPANY);
            },
            'orders AS total_expired_count' => function (Builder $query) use ($now) {
                $query->whereNotNull('is_paid_at');
                $query->where('expires_at', '<', $now);
            },
            'orders AS total_active_count' => function (Builder $query) use ($now) {
                $query->whereNotNull('is_paid_at');
                $query->where('expires_at', '>', $now);
            }
        ])->first()->toArray();

        return api()->success(null, [
            'total_orders' => $total_counts['total_orders'],
            'total_paid' => $total_counts['total_paid'],
            'total_resolved' => $total_counts['total_resolved'],
            'total_refund' => $total_counts['total_refund'],
            'total_commission_paid' => $total_counts['total_commission_paid'],
            'total_paid_with_wallet' => $total_counts['total_paid_with_wallet'],
            'total_paid_with_giftcode' => $total_counts['total_paid_with_giftcode'],
            'total_paid_with_purchase' => $total_counts['total_paid_with_purchase'],
            'total_plan_start' => $total_counts['total_plan_start'],
            'total_plan_purchase' => $total_counts['total_plan_purchase'],
            'total_plan_special' => $total_counts['total_plan_special'],
            'total_plan_company' => $total_counts['total_plan_company'],
            'total_expired_count' => $total_counts['total_expired_count'],
            'total_active_count' => $total_counts['total_active_count'],
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
            /**@var $user User */
            $user = auth()->user();

            DB::beginTransaction();
            $order_db = Order::query()->create([
                "user_id" => $user->id,
                "payment_type" => $request->get('payment_type'),
                "payment_currency" => $request->has('payment_currency') ? $request->get('payment_currency') : null,
                "payment_driver" => $request->has('payment_driver') ? $request->get('payment_driver') : null,
                "package_id" => $request->get('package_id'),
                'validity_in_days' => $package->getValidityInDays(),
                'plan' => $user->paidOrders()->exists() ? ORDER_PLAN_PURCHASE : ORDER_PLAN_START
            ]);
            $order_db->refreshOrder();
            //Order Resolver
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

            list($payment_flag, $payment_response) = PaymentFacade::pay($invoice_request);


            if (!$payment_flag)
                throw new \Exception($payment_response, 406);

            if ($request->get('payment_type') != 'purchase') {

                $now = now()->toDateTimeString();

                $order_service = $order_db->fresh()->getOrderService();
                $order_service->setIsPaidAt($now);
                $order_service->setIsResolvedAt($now);

                $submit_response = MlmClientFacade::submitOrder($order_service);

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
