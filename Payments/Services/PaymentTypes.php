<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: payments.proto

namespace Payments\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>payments.services.PaymentTypes</code>
 */
class PaymentTypes extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.payments.services.PaymentType payment_types = 1;</code>
     */
    protected $payment_types = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Payments\Services\PaymentType $payment_types
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Payments::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.payments.services.PaymentType payment_types = 1;</code>
     * @return \Payments\Services\PaymentType|null
     */
    public function getPaymentTypes()
    {
        return $this->payment_types;
    }

    public function hasPaymentTypes()
    {
        return isset($this->payment_types);
    }

    public function clearPaymentTypes()
    {
        unset($this->payment_types);
    }

    /**
     * Generated from protobuf field <code>.payments.services.PaymentType payment_types = 1;</code>
     * @param \Payments\Services\PaymentType $var
     * @return $this
     */
    public function setPaymentTypes($var)
    {
//        GPBUtil::checkMessage($var, \Payments\Services\PaymentType::class);
        $this->payment_types = $var;
        return $this;
    }

}

