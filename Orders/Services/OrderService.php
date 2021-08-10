<?php


namespace Orders\Services;


use Payments\Services\EmptyObject;
use Payments\Services\PaymentService;
use User\Services\User;

class OrderService implements OrdersServiceInterface
{

    private $payment_service;

    public function __construct(PaymentService $payment_service)
    {
        $this->payment_service = $payment_service;
    }

    /**
     * @inheritDoc
     */
    public function OrderById(Id $request): Order
    {
        $response = new Order();
        $order = \Orders\Models\Order::query()->find($request->getId());
        $response->setUserId((int)$order->user_id);
        $response->setToUserId((int)$order->to_user_id);
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

    public function getPaymentCurrencies(){
        $empty_object = new EmptyObject();
        return $this->payment_service->getPaymentCurrencies($empty_object);
    }

    public function getPaymentTypes(){
        $empty_object = new EmptyObject();
        return $this->payment_service->getPaymentTypes($empty_object);
    }

}
