<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: payments.proto

namespace Payments\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>payments.services.PaymentCurrencies</code>
 */
class PaymentCurrencies extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .payments.services.PaymentCurrency payment_currencies = 1;</code>
     */
    private $payment_currencies;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Payments\Services\PaymentCurrency[]|\Google\Protobuf\Internal\RepeatedField $payment_currencies
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Payments::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .payments.services.PaymentCurrency payment_currencies = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPaymentCurrencies()
    {
        return $this->payment_currencies;
    }

    /**
     * Generated from protobuf field <code>repeated .payments.services.PaymentCurrency payment_currencies = 1;</code>
     * @param \Payments\Services\PaymentCurrency[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPaymentCurrencies($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Payments\Services\PaymentCurrency::class);
        $this->payment_currencies = $arr;
        return $this->payment_currencies;
    }

}

