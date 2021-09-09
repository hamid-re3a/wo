<?php


namespace Orders\Services;


use Orders\Repository\OrderRepository;
use Packages\Services\PackageService;
use Payments\Services\EmptyObject;
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

        if ($order) {
            $response->setId($order->id);
            $response->setUserId((int)$order->user_id);
            $response->setToUserId((int)$order->to_user_id);
            $response->setPackageId((int)$order->package_id);
            $response->setTotalCostInUsd((double)$order->total_cost_in_usd);
            $response->setPackagesCostInUsd((double)$order->packages_cost_in_usd);
            $response->setRegistrationFeeInUsd((double)$order->registration_fee_in_usd);
            $response->setIsPaidAt((string)$order->is_paid_at);
            $response->setIsResolvedAt((string)$order->is_resolved_at);
            $response->setIsRefundAt((string)$order->is_refund_at);
            $response->setValidityInDays((string)$order->validity_in_days);
            $response->setIsCommissionResolvedAt((string)$order->is_commission_resolved_at);
            $response->setPaymentType((string)$order->payment_type);
            $response->setPaymentCurrency((string)$order->payment_currency);
            $response->setPaymentDriver((string)$order->payment_driver);
            $response->setPlan((string)$order->plan);
            $response->setUser($order->user->getUserService());
            $response->setToUser($order->toUser->getUserService());

            $id = new \Packages\Services\Id();
            $id->setId($order->package_id);
            $response->setPackage($this->package_service->packageFullById($id));

        }

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
            'is_commission_resolved_at' => !empty($order->getIsCommissionResolvedAt()) ? $order->getIsCommissionResolvedAt() : $order_db->is_commission_resolved_at,
            'payment_type' => !empty($order->getPaymentType()) ? $order->getPaymentType() : $order_db->payment_type,
            'payment_currency' => !empty($order->getPaymentCurrency()) ? $order->getPaymentCurrency() : $order_db->payment_currency,
            'payment_driver' => !empty($order->getPaymentDriver()) ? $order->getPaymentDriver() : $order_db->payment_driver,
            'plan' => !empty($order->getPlan()) ? $order->getPlan() : $order_db->plan,
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

}
