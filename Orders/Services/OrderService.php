<?php


namespace Orders\Services;


use Illuminate\Support\Carbon;
use Orders\Repository\OrderRepository;
use Orders\Services\Grpc\Id;
use Orders\Services\Grpc\Order;
use Orders\Services\Grpc\OrderPlans;
use Packages\Models\Category;
use Packages\Services\PackageService;
use Payments\Services\PaymentService;

class OrderService implements OrdersServiceInterface
{

    private $payment_service;
    private $package_service;
    private $order_repository;

    public function __construct(PaymentService $payment_service, OrderRepository $order_repository, PackageService $package_service)
    {
        $this->payment_service = $payment_service;
        $this->order_repository = $order_repository;
        $this->package_service = $package_service;
    }

    /**
     * @inheritDoc
     */
    public function OrderById(Id $request): Order
    {
        $response = new Order();
        $order = \Orders\Models\Order::query()->find($request->getId());

        if ($order)
            return $order->getGrpcMessage();

        return $response;

    }

    public function updateOrder(Order $order): Order
    {
        $order_db = \Orders\Models\Order::query()->find($order->getId());
        $order_db->update([
            'user_id' => !empty($order->getUserId()) ? $order->getUserId() : $order_db->user_id,
            'from_user_id' => !empty($order->getFromUserId()) ? $order->getFromUserId() : $order_db->from_user_id,
            'package_id' => !empty($order->getPackageId()) ? $order->getPackageId() : $order_db->package_id,
            'total_cost_in_pf' => !empty($order->getTotalCostInPf()) ? $order->getTotalCostInPf() : $order_db->total_cost_in_pf,
            'packages_cost_in_pf' => !empty($order->getPackagesCostInPf()) ? $order->getPackagesCostInPf() : $order_db->packages_cost_in_pf,
            'registration_fee_in_pf' => !empty($order->getRegistrationFeeInPf()) ? $order->getRegistrationFeeInPf() : $order_db->registration_fee_in_pf,
            'is_paid_at' => !empty($order->getIsPaidAt()) ? $order->getIsPaidAt() : $order_db->is_paid_at,
            'is_resolved_at' => !empty($order->getIsResolvedAt()) ? $order->getIsResolvedAt() : $order_db->is_resolved_at,
            'is_refund_at' => !empty($order->getIsRefundAt()) ? $order->getIsRefundAt() : $order_db->is_refund_at,
            'is_commission_resolved_at' => !empty($order->getIsCommissionResolvedAt()) ? $order->getIsCommissionResolvedAt() : $order_db->is_commission_resolved_at,
            'payment_type' => !empty($order->getPaymentType()) ? $order->getPaymentType() : $order_db->payment_type,
            'payment_currency' => !empty($order->getPaymentCurrency()) ? $order->getPaymentCurrency() : $order_db->payment_currency,
            'payment_driver' => !empty($order->getPaymentDriver()) ? $order->getPaymentDriver() : $order_db->payment_driver,
            'plan' => !empty($order->getPlan()) ? OrderPlans::name($order->getPlan()) : $order_db->plan,
        ]);
        return $order;
    }

    public function getActiveOrdersSum()
    {
        return $this->order_repository->getActiveOrdersSum();
    }

    public function getPaidOrdersSum()
    {
        return $this->order_repository->getPaidOrdersSum();
    }

    public function getCountOrders()
    {
        return $this->order_repository->getCountOrders();
    }

    public function getActiveOrdersCount()
    {
        return $this->order_repository->getActiveOrdersCount();
    }

    public function getExpiredOrders()
    {
        return $this->order_repository->getExpiredOrders();
    }

    public function packageOverviewCountChart($type)
    {
        $that = $this;
        $function_active_package = function ($from_day, $to_day) use ($that) {
            return $that->order_repository->getActivePackageByDateCollection($from_day, $to_day);
        };
        $function_expired_package = function ($from_day, $to_day) use ($that) {
            return $that->order_repository->getExpiredPackageByDateCollection($from_day, $to_day);
        };
        $function_all_package = function ($from_day, $to_day) use ($that) {
            return $that->order_repository->getTotalPackageByDateCollection($from_day, $to_day);
        };


        $sub_function = function ($collection, $intervals) {
            return $collection->whereBetween('created_at', $intervals)->count();
        };

        $final_result = [];
        $final_result['active'] = chartMaker($type, $function_active_package, $sub_function);
        $final_result['expired'] = chartMaker($type, $function_expired_package, $sub_function);
        $final_result['all'] = chartMaker($type, $function_all_package, $sub_function);
        return $final_result;
    }

    public function packageOverviewCountForUser($type,$user)
    {
        $that = $this;
        $function_active_package = function ($from_day, $to_day) use ($that,$user) {
            return $that->order_repository->getExpiredPackageByDateForUserCollection($from_day, $to_day,$user);
        };
        $function_expired_package = function ($from_day, $to_day) use ($that,$user) {
            return $that->order_repository->getExpiredPackageByDateForUserCollection($from_day, $to_day,$user);
        };
        $function_all_package = function ($from_day, $to_day) use ($that,$user) {
            return $that->order_repository->getExpiredPackageByDateForUserCollection($from_day, $to_day,$user);
        };


        $sub_function = function ($collection, $intervals) {
            return $collection->whereBetween('created_at', $intervals)->count();
        };

        $final_result = [];
        $final_result['active'] = chartMaker($type, $function_active_package, $sub_function);
        $final_result['expired'] = chartMaker($type, $function_expired_package, $sub_function);
        $final_result['all'] = chartMaker($type, $function_all_package, $sub_function);
        return $final_result;
    }
    public function packageTypeCountChart($type)
    {
        $that = $this;
        $function_active_package = function ($from_day, $to_day) use ($that) {
            return $that->order_repository->getActiveOrderWithPackageByDateCollection($from_day, $to_day);
        };

        $sub_function_B = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'B')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };
        $sub_function_I = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'I')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };

        $sub_function_A = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'A')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };

        $sub_function_P = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'P')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };

        $final_result = [];
        $final_result['B'] = chartMaker($type, $function_active_package, $sub_function_B);
        $final_result['I'] = chartMaker($type, $function_active_package, $sub_function_I);
        $final_result['A'] = chartMaker($type, $function_active_package, $sub_function_A);
        $final_result['P'] = chartMaker($type, $function_active_package, $sub_function_P);
        return $final_result;
    }

    public function packageTypeCountForUser($type,$user)
    {
        $that = $this;
        $function_active_package = function ($from_day, $to_day) use ($that,$user) {
            return $that->order_repository->getActiveOrderWithPackageByDateForUserCollection($from_day, $to_day,$user);
        };

        $sub_function_B = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'B')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };
        $sub_function_I = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'I')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };

        $sub_function_A = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'A')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };

        $sub_function_P = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'P')->first();
            $packages = $category->packages()->pluck('id');
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count();
        };

        $final_result = [];
        $final_result['B'] = chartMaker($type, $function_active_package, $sub_function_B);
        $final_result['I'] = chartMaker($type, $function_active_package, $sub_function_I);
        $final_result['A'] = chartMaker($type, $function_active_package, $sub_function_A);
        $final_result['P'] = chartMaker($type, $function_active_package, $sub_function_P);
        return $final_result;
    }

    public function packageTypePercentageCountChart($type)
    {
        $that = $this;
        $function_active_package = function ($from_day, $to_day) use ($that) {
            return $that->order_repository->getActiveOrderWithPackageByDateCollection($from_day, $to_day);
        };

        $sub_function_B = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'B')->first();
            $packages = $category->packages()->pluck('id');
            $all_count = $collection->whereBetween('created_at', $intervals)->count();
            if ($all_count <= 0)
                return 0;
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count() / $all_count;
        };
        $sub_function_I = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'I')->first();
            $packages = $category->packages()->pluck('id');
            $all_count = $collection->whereBetween('created_at', $intervals)->count();
            if ($all_count <= 0)
                return 0;
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count() / $all_count;
        };

        $sub_function_A = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'A')->first();
            $packages = $category->packages()->pluck('id');
            $all_count = $collection->whereBetween('created_at', $intervals)->count();
            if ($all_count <= 0)
                return 0;
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count() / $all_count;
        };

        $sub_function_P = function ($collection, $intervals) {
            $category = Category::query()->where('short_name', 'P')->first();
            $packages = $category->packages()->pluck('id');
            $all_count = $collection->whereBetween('created_at', $intervals)->count();
            if ($all_count <= 0)
                return 0;
            return $collection->whereIn('package_id', $packages)->whereBetween('created_at', $intervals)->count() / $all_count;
        };

        $final_result = [];
        $final_result['B'] = chartMaker($type, $function_active_package, $sub_function_B);
        $final_result['I'] = chartMaker($type, $function_active_package, $sub_function_I);
        $final_result['A'] = chartMaker($type, $function_active_package, $sub_function_A);
        $final_result['P'] = chartMaker($type, $function_active_package, $sub_function_P);
        return $final_result;
    }

}
