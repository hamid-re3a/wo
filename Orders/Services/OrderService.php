<?php


namespace Orders\Services;


use Illuminate\Support\Carbon;
use Orders\Repository\OrderRepository;
use Packages\Services\PackageService;
use Payments\Services\EmptyObject;
use Payments\Services\PaymentService;
use User\Services\User;

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
        $response->setId($order->id);
        $response->setUserId((int)$order->user_id);
        $response->setToUserId((int)$order->to_user_id);
        $response->setPackageId((int)$order->package_id);
        $response->setTotalCostInUsd((double)$order->total_cost_in_usd);
        $response->setPackagesCostInUsd((double)$order->packages_cost_in_usd);
        $response->setRegistrationFeeInUsd((double)$order->registration_fee_in_usd);
        $response->setIsPaidAt($order->is_paid_at);
        $response->setIsResolvedAt($order->is_resolved_at);
        $response->setIsRefundAt($order->is_refund_at);
        $response->setIsExpiredAt($order->is_expired_at);
        $response->setIsCommissionResolvedAt($order->is_commission_resolved_at);
        $response->setPaymentType($order->payment_type);
        $response->setPaymentCurrency($order->payment_currency);
        $response->setPaymentDriver($order->payment_driver);
        $response->setPlan($order->plan);


        $user_response = new User();
        $user_response->setId($order->user->id);
        $user_response->setEmail($order->user->email);
        $user_response->setUsername($order->user->username);
        $user_response->setFirstName($order->user->first_name);
        $user_response->setLastName($order->user->last_name);

        $response->setUser($user_response);

        $to_user_response = new User();
        $to_user_response->setId($order->toUser->id);
        $to_user_response->setEmail($order->toUser->email);
        $to_user_response->setUsername($order->toUser->username);
        $to_user_response->setFirstName($order->toUser->first_name);
        $to_user_response->setLastName($order->toUser->last_name);

        $response->setToUser($to_user_response);

        return $response;
    }

    public function getPaymentCurrencies()
    {
        $empty_object = new EmptyObject();
        return $this->payment_service->getPaymentCurrencies($empty_object);
    }

    public function getPaymentTypes()
    {
        $empty_object = new EmptyObject();
        return $this->payment_service->getPaymentTypes($empty_object);
    }

    public function updateOrder(Order $order): Order
    {
        $order_db = \Orders\Models\Order::query()->find($order->getId());
        $order_db->update([
            'user_id' => !empty($order->getUserId()) ? $order->getUserId() : $order_db->user_id,
            'to_user_id' => !empty($order->getToUserId()) ? $order->getToUserId() : $order_db->to_user_id,
            'package_id' => !empty($order->getPackageId()) ? $order->getPackageId() : $order_db->package_id,
            'total_cost_in_usd' => !empty($order->getTotalCostInUsd()) ? $order->getTotalCostInUsd() : $order_db->total_cost_in_usd,
            'packages_cost_in_usd' => !empty($order->getPackagesCostInUsd()) ? $order->getPackagesCostInUsd() : $order_db->packages_cost_in_usd,
            'registration_fee_in_usd' => !empty($order->getRegistrationFeeInUsd()) ? $order->getRegistrationFeeInUsd() : $order_db->registration_fee_in_usd,
            'is_paid_at' => !empty($order->getIsPaidAt()) ? $order->getIsPaidAt() : $order_db->is_paid_at,
            'is_resolved_at' => !empty($order->getIsResolvedAt()) ? $order->getIsResolvedAt() : $order_db->is_resolved_at,
            'is_refund_at' => !empty($order->getIsRefundAt()) ? $order->getIsRefundAt() : $order_db->is_refund_at,
            'is_expired_at' => !empty($order->getIsExpiredAt()) ? $order->getIsExpiredAt() : $order_db->is_expired_at,
            'is_commission_resolved_at' => !empty($order->getIsCommissionResolvedAt()) ? $order->getIsCommissionResolvedAt() : $order_db->is_commission_resolved_at,
            'payment_type' => !empty($order->getPaymentType()) ? $order->getPaymentType() : $order_db->payment_type,
            'payment_currency' => !empty($order->getPaymentCurrency()) ? $order->getPaymentCurrency() : $order_db->payment_currency,
            'payment_driver' => !empty($order->getPaymentDriver()) ? $order->getPaymentDriver() : $order_db->payment_driver,
            'plan' => !empty($order->getPlan()) ? $order->getPlan() : $order_db->plan,
        ]);
        return $order;
    }


    public function startOrder(Order $order): Order
    {
        $order_db = \Orders\Models\Order::query()->find($order->getId());
        $id = new \Packages\Services\Id;
        $id->setId($order_db->package_id);
        $package_model = app(PackageService::class)->packageById($id);
        if ($order->getPlan() == ORDER_PLAN_UPGRADE) {
            $order_db->update([
                'is_paid_at' => now(),
            ]);
        } else
            $order_db->update([
                'is_paid_at' => now(),
                'is_expired_at' => now()->addDays($package_model->getValidityInDays()),
            ]);

        $order->setIsPaidAt($order_db->is_paid_at);
        $order->setIsExpiredAt($order_db->is_expired_at);

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
