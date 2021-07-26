<?php
# Generated by the protocol buffer compiler (https://github.com/mix-php/grpc). DO NOT EDIT!
# source: payments.proto

namespace Payments\Services;

use Mix\Grpc;
use Mix\Grpc\Context;
use Orders\Services;

interface PaymentsServiceInterface extends Grpc\ServiceInterface
{
    // GRPC specific service name.
    public const NAME = "payments.services.PaymentsService";

    /**
    * @param Context $context
    * @param Id $request
    * @return Payment
    *
    * @throws Grpc\Exception\InvokeException
    */
    public function paymentById(Context $context, Id $request): Payment;

    /**
    * @param Context $context
    * @param Services\Order $request
    * @return Payment
    *
    * @throws Grpc\Exception\InvokeException
    */
    public function pay(Context $context, Services\Order $request): Payment;

    /**
    * @param Context $context
    * @param Services\Order $request
    * @return Payment
    *
    * @throws Grpc\Exception\InvokeException
    */
    public function rollbackPay(Context $context, Services\Order $request): Payment;

    /**
    * @param Context $context
    * @param Services\Order $request
    * @return Payment
    *
    * @throws Grpc\Exception\InvokeException
    */
    public function refund(Context $context, Services\Order $request): Payment;

    /**
    * @param Context $context
    * @param Id $request
    * @return Payments
    *
    * @throws Grpc\Exception\InvokeException
    */
    public function paymentsByUserId(Context $context, Id $request): Payments;
}
