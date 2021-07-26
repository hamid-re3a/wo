<?php

namespace App\Services\Payment;

use Mix\Grpc\Context;
use Orders\Services;
use Payments\Services\GrpcMainService;
use Payments\Services\Id;
use Payments\Services\Payments;
use Payments\Services\PaymentsServiceInterface;

class PaymentService extends GrpcMainService implements PaymentsServiceInterface
{
    /**
     * @inheritDoc
     */
    public function paymentById(Context $context, Id $request): \Payments\Services\Payment
    {
        // TODO: Implement paymentById() method.
    }

    /**
     * @inheritDoc
     */
    public function pay(Context $context, Services\Order $request): \Payments\Services\Payment
    {
        // TODO: Implement pay() method.
    }

    /**
     * @inheritDoc
     */
    public function rollbackPay(Context $context, Services\Order $request): \Payments\Services\Payment
    {
        // TODO: Implement rollbackPay() method.
    }

    /**
     * @inheritDoc
     */
    public function refund(Context $context, Services\Order $request): \Payments\Services\Payment
    {
        // TODO: Implement refund() method.
    }

    /**
     * @inheritDoc
     */
    public function paymentsByUserId(Context $context, Id $request): Payments
    {
        // TODO: Implement paymentsByUserId() method.
    }
}
