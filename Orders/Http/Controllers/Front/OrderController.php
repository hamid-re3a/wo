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
use Orders\Services\OrderResolver;
use Packages\Services\Id;
use Packages\Services\PackageService;
use Payments\Services\Invoice;
use Payments\Services\PaymentService;

class OrderController extends Controller
{
    use  ValidatesRequests;

    private $payment_service;
    private $package_service;

    public function __construct(PaymentService $payment_service, PackageService $package_service)
    {
        $this->payment_service = $payment_service;
        $this->package_service = $package_service;
    }

    /**
     * Counts
     * @group
     * Public User > Orders
     */
    public function counts()
    {
        $total_counts = request()->user->withCount([
            'orders AS total_orders',
            'orders AS total_paid' => function (Builder $query) {
                $query->whereNotNull('is_paid_at');
            },
            'orders AS total_resolved' => function(Builder $query) {
                $query->whereNotNull('is_resolved_at');
            },
            'orders AS total_refund' => function (Builder $query) {
                $query->whereNotNull('is_refund_at');
            },
            'orders AS total_commission_paid' => function(Builder $query) {
                $query->whereNotNull('is_commission_resolved_at');
            },
            'orders AS total_paid_with_wallet' => function(Builder $query) {
                $query->where('payment_driver','deposit');
            },
            'orders AS total_paid_with_giftcode' => function(Builder $query) {
                $query->where('payment_driver', 'giftcode');
            },
            'orders AS total_paid_with_purchase' => function(Builder $query) {
                $query->where('payment_driver', 'purchase');
            },
            'orders AS total_plan_start' => function(Builder $query) {
                $query->where('plan',ORDER_PLAN_START);
            },
            'orders AS total_plan_purchase' => function(Builder $query) {
                $query->where('plan',ORDER_PLAN_PURCHASE);
            }
        ])->first()->toArray();

        return api()->success(null,[
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
        $orders = $request->user->orders()->filter()->simplePaginate();
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
        $order = $request->user->orders()->find($request->get('id'))->first();
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
        $package = $this->validatePackage($request);
        try {
            DB::beginTransaction();
            $order_db = Order::query()->create([
                "user_id" => $request->header('X-user-id'),
                "payment_type" => $request->get('payment_type'),
                "payment_currency" => $request->has('payment_currency') ? $request->get('payment_currency') : null,
                "payment_driver" => $request->has('payment_driver') ? $request->get('payment_driver') : null,
                "package_id" => $request->get('package_id'),
                'validity_in_days' => $package->getValidityInDays(),
                'plan' => $request->get('plan')
            ]);

            $order_db->refreshOrder();

            //Order Resolver
            $order_resolver = new OrderResolver($order_db->getOrderService());
            list($flag, $response) = $order_resolver->simulateValidation();

            if ($flag === false)
                throw new \Exception($response, 406);

            //Invoice service
            $invoice_request = new Invoice();
            $invoice_request->setPayableId((int)$order_db->id);
            $invoice_request->setPayableType('Order');
            $invoice_request->setPfAmount($order_db->total_cost_in_usd);
            $invoice_request->setPaymentDriver($order_db->payment_driver);
            $invoice_request->setPaymentType($order_db->payment_type);
            $invoice_request->setPaymentCurrency($order_db->payment_currency);
            $invoice_request->setUser($request->user->getUserService());
            $invoice_request->setUserId($request->user->id);
            $invoice_request->setPayableId($order_db->id);
            $invoice_request->setPayableType('Order');

            list($flag, $payment_response) = $this->payment_service->pay($invoice_request);


            if (!$flag)
                throw new \Exception($response, 406);

            if ($request->get('payment_type') != 'purchase') {
                list($flag, $response) = $order_resolver->resolve();
                if (!$flag)
                    throw new \Exception($response, 406);

                $order_db->update([
                    'is_resolved_at' => now()->toDateTimeString()
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
