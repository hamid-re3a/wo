<?php


namespace Orders\Services;


use Illuminate\Support\Carbon;
use Orders\Repository\OrderRepository;
use Orders\Services\Grpc\Id;
use Orders\Services\Grpc\Order;
use Orders\Services\Grpc\OrderPlans;
use Packages\Services\PackageService;
use Payments\Services\Grpc\EmptyObject;
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
            return $order->getOrderService();

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

    public function getCountPackageSubscriptions()
    {
        return collect(["count" => $this->order_repository->getCountSubscriptions()]);
    }

    public function activePackageCount()
    {
        $count = $this->order_repository->getCountActivePackage();
        return collect(["count" => $count]);
    }

    public function deactivatePackageCount()
    {
        $count = $this->order_repository->getCountDeactivatePackage();
        return collect(["count" => $count]);
    }

    public function packageOverviewCount($type)
    {
        switch ($type) {
            case "week":
                $array_active = $this->order_repository->getCountActivePackageByDate(Carbon::now()->endOfDay()->subDay(7), Carbon::now());
                $array_deactivate = $this->order_repository->getCountDeactivatePackageByDate(Carbon::now()->endOfDay()->subDay(7), Carbon::now());
                $total = $this->order_repository->getCountTotalPackageByDate(Carbon::now()->endOfDay()->subDay(7), Carbon::now());
                $array_count = [];
                foreach (range(1, 7) as $day) {
                    $array_count[] = ["time" => Carbon::now()->startOfDay()->subDay($day)->timestamp, "active" => $array_active->whereBetween('created_at', [Carbon::now()->startOfDay()->subDay($day), Carbon::now()->endOfDay()->subDay($day)])->count()];
                    $array_count[] = ["time" => Carbon::now()->startOfDay()->subDay($day)->timestamp, "deactivate" => $array_deactivate->whereBetween('created_at', [Carbon::now()->startOfDay()->subDay($day), Carbon::now()->endOfDay()->subDay($day)])->count()];
                    $array_count[] = ["time" => Carbon::now()->startOfDay()->subDay($day)->timestamp, "total" => $total->whereBetween('created_at', [Carbon::now()->startOfDay()->subDay($day), Carbon::now()->endOfDay()->subDay($day)])->count()];
                }
                return $array_count;
                break;
            case "month":
                $array_active = $this->order_repository->getCountActivePackageByDate(Carbon::now()->endOfMonth()->subMonth(6), Carbon::now());
                $array_deactivate = $this->order_repository->getCountDeactivatePackageByDate(Carbon::now()->endOfMonth()->subMonth(6), Carbon::now());
                $total = $this->order_repository->getCountTotalPackageByDate(Carbon::now()->endOfMonth()->subMonth(6), Carbon::now());
                $array_count = [];
                foreach (range(6, 12) as $month) {
                    $array_count[] = ["time" => Carbon::now()->startOfMonth()->subMonth($month)->timestamp, "active" => $array_active->whereBetween('created_at', [Carbon::now()->startOfMonth()->subMonth($month), Carbon::now()->endOfMonth()->subMonth($month)])->count()];
                    $array_count[] = ["time" => Carbon::now()->startOfMonth()->subMonth($month)->timestamp, "deactivate" => $array_deactivate->whereBetween('created_at', [Carbon::now()->startOfMonth()->subMonth($month), Carbon::now()->endOfMonth()->subMonth($month)])->count()];
                    $array_count[] = ["time" => Carbon::now()->startOfMonth()->subMonth($month)->timestamp, "total" => $total->whereBetween('created_at', [Carbon::now()->startOfMonth()->subMonth($month), Carbon::now()->endOfMonth()->subMonth($month)])->count()];
                }
                return $array_count;
                break;
            case "year":
                $array_active = $this->order_repository->getCountActivePackageByDate(Carbon::now()->endOfYear()->subYear(3), Carbon::now());
                $array_deactivate = $this->order_repository->getCountDeactivatePackageByDate(Carbon::now()->endOfYear()->subYear(3), Carbon::now());
                $total = $this->order_repository->getCountTotalPackageByDate(Carbon::now()->endOfDay()->subYear(3), Carbon::now());
                $array_count = [];
                foreach (range(1, 3) as $year) {
                    $array_count[] = ["time" => Carbon::now()->startOfYear()->subYear($year)->timestamp, "active" => $array_active->whereBetween('created_at', [Carbon::now()->startOfYear()->subYear($year), Carbon::now()->endOfYear()->subYear($year)])->count()];
                    $array_count[] = ["time" => Carbon::now()->startOfYear()->subYear($year)->timestamp, "deactivate" => $array_deactivate->whereBetween('created_at', [Carbon::now()->startOfYear()->subYear($year), Carbon::now()->endOfYear()->subYear($year)])->count()];
                    $array_count[] = ["time" => Carbon::now()->startOfYear()->subYear($year)->timestamp, "total" => $total->whereBetween('created_at', [Carbon::now()->startOfYear()->subYear($year), Carbon::now()->endOfYear()->subYear($year)])->count()];
                }
                return $array_count;
                break;
        }

    }

}
