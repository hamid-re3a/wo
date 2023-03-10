<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: orders.proto

namespace Orders\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>orders.services.Order</code>
 */
class Order extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int64 id = 1;</code>
     */
    protected $id = 0;
    /**
     * Generated from protobuf field <code>int64 user_id = 2;</code>
     */
    protected $user_id = 0;
    /**
     * Generated from protobuf field <code>int64 to_user_id = 3;</code>
     */
    protected $to_user_id = 0;
    /**
     * Generated from protobuf field <code>double total_cost_in_usd = 4;</code>
     */
    protected $total_cost_in_usd = 0.0;
    /**
     * Generated from protobuf field <code>double packages_cost_in_usd = 5;</code>
     */
    protected $packages_cost_in_usd = 0.0;
    /**
     * Generated from protobuf field <code>double registration_fee_in_usd = 6;</code>
     */
    protected $registration_fee_in_usd = 0.0;
    /**
     * Generated from protobuf field <code>string is_paid_at = 7;</code>
     */
    protected $is_paid_at = '';
    /**
     * Generated from protobuf field <code>string is_resolved_at = 8;</code>
     */
    protected $is_resolved_at = '';
    /**
     * Generated from protobuf field <code>string is_refund_at = 9;</code>
     */
    protected $is_refund_at = '';
    /**
     * Generated from protobuf field <code>string is_expired_at = 10;</code>
     */
    protected $is_expired_at = '';
    /**
     * Generated from protobuf field <code>string is_commission_resolved_at = 11;</code>
     */
    protected $is_commission_resolved_at = '';
    /**
     * Generated from protobuf field <code>string payment_type = 12;</code>
     */
    protected $payment_type = '';
    /**
     * Generated from protobuf field <code>string payment_currency = 13;</code>
     */
    protected $payment_currency = '';
    /**
     * Generated from protobuf field <code>string payment_driver = 14;</code>
     */
    protected $payment_driver = '';
    /**
     * Generated from protobuf field <code>string plan = 15;</code>
     */
    protected $plan = '';
    /**
     * Generated from protobuf field <code>string deleted_at = 16;</code>
     */
    protected $deleted_at = '';
    /**
     * Generated from protobuf field <code>string created_at = 17;</code>
     */
    protected $created_at = '';
    /**
     * Generated from protobuf field <code>string updated_at = 18;</code>
     */
    protected $updated_at = '';
    /**
     * Generated from protobuf field <code>.orders.services.User user = 19;</code>
     */
    protected $user = null;
    /**
     * Generated from protobuf field <code>.orders.services.User to_user = 20;</code>
     */
    protected $to_user = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $id
     *     @type int|string $user_id
     *     @type int|string $to_user_id
     *     @type float $total_cost_in_usd
     *     @type float $packages_cost_in_usd
     *     @type float $registration_fee_in_usd
     *     @type string $is_paid_at
     *     @type string $is_resolved_at
     *     @type string $is_refund_at
     *     @type string $is_expired_at
     *     @type string $is_commission_resolved_at
     *     @type string $payment_type
     *     @type string $payment_currency
     *     @type string $payment_driver
     *     @type string $plan
     *     @type string $deleted_at
     *     @type string $created_at
     *     @type string $updated_at
     *     @type \Orders\Services\User $user
     *     @type \Orders\Services\User $to_user
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Orders::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int64 id = 1;</code>
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Generated from protobuf field <code>int64 id = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setId($var)
    {
        GPBUtil::checkInt64($var);
        $this->id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int64 user_id = 2;</code>
     * @return int|string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Generated from protobuf field <code>int64 user_id = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setUserId($var)
    {
        GPBUtil::checkInt64($var);
        $this->user_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int64 to_user_id = 3;</code>
     * @return int|string
     */
    public function getToUserId()
    {
        return $this->to_user_id;
    }

    /**
     * Generated from protobuf field <code>int64 to_user_id = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setToUserId($var)
    {
        GPBUtil::checkInt64($var);
        $this->to_user_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>double total_cost_in_usd = 4;</code>
     * @return float
     */
    public function getTotalCostInUsd()
    {
        return $this->total_cost_in_usd;
    }

    /**
     * Generated from protobuf field <code>double total_cost_in_usd = 4;</code>
     * @param float $var
     * @return $this
     */
    public function setTotalCostInUsd($var)
    {
        GPBUtil::checkDouble($var);
        $this->total_cost_in_usd = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>double packages_cost_in_usd = 5;</code>
     * @return float
     */
    public function getPackagesCostInUsd()
    {
        return $this->packages_cost_in_usd;
    }

    /**
     * Generated from protobuf field <code>double packages_cost_in_usd = 5;</code>
     * @param float $var
     * @return $this
     */
    public function setPackagesCostInUsd($var)
    {
        GPBUtil::checkDouble($var);
        $this->packages_cost_in_usd = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>double registration_fee_in_usd = 6;</code>
     * @return float
     */
    public function getRegistrationFeeInUsd()
    {
        return $this->registration_fee_in_usd;
    }

    /**
     * Generated from protobuf field <code>double registration_fee_in_usd = 6;</code>
     * @param float $var
     * @return $this
     */
    public function setRegistrationFeeInUsd($var)
    {
        GPBUtil::checkDouble($var);
        $this->registration_fee_in_usd = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string is_paid_at = 7;</code>
     * @return string
     */
    public function getIsPaidAt()
    {
        return $this->is_paid_at;
    }

    /**
     * Generated from protobuf field <code>string is_paid_at = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setIsPaidAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->is_paid_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string is_resolved_at = 8;</code>
     * @return string
     */
    public function getIsResolvedAt()
    {
        return $this->is_resolved_at;
    }

    /**
     * Generated from protobuf field <code>string is_resolved_at = 8;</code>
     * @param string $var
     * @return $this
     */
    public function setIsResolvedAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->is_resolved_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string is_refund_at = 9;</code>
     * @return string
     */
    public function getIsRefundAt()
    {
        return $this->is_refund_at;
    }

    /**
     * Generated from protobuf field <code>string is_refund_at = 9;</code>
     * @param string $var
     * @return $this
     */
    public function setIsRefundAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->is_refund_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string is_expired_at = 10;</code>
     * @return string
     */
    public function getIsExpiredAt()
    {
        return $this->is_expired_at;
    }

    /**
     * Generated from protobuf field <code>string is_expired_at = 10;</code>
     * @param string $var
     * @return $this
     */
    public function setIsExpiredAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->is_expired_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string is_commission_resolved_at = 11;</code>
     * @return string
     */
    public function getIsCommissionResolvedAt()
    {
        return $this->is_commission_resolved_at;
    }

    /**
     * Generated from protobuf field <code>string is_commission_resolved_at = 11;</code>
     * @param string $var
     * @return $this
     */
    public function setIsCommissionResolvedAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->is_commission_resolved_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string payment_type = 12;</code>
     * @return string
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * Generated from protobuf field <code>string payment_type = 12;</code>
     * @param string $var
     * @return $this
     */
    public function setPaymentType($var)
    {
        GPBUtil::checkString($var, True);
        $this->payment_type = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string payment_currency = 13;</code>
     * @return string
     */
    public function getPaymentCurrency()
    {
        return $this->payment_currency;
    }

    /**
     * Generated from protobuf field <code>string payment_currency = 13;</code>
     * @param string $var
     * @return $this
     */
    public function setPaymentCurrency($var)
    {
        GPBUtil::checkString($var, True);
        $this->payment_currency = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string payment_driver = 14;</code>
     * @return string
     */
    public function getPaymentDriver()
    {
        return $this->payment_driver;
    }

    /**
     * Generated from protobuf field <code>string payment_driver = 14;</code>
     * @param string $var
     * @return $this
     */
    public function setPaymentDriver($var)
    {
        GPBUtil::checkString($var, True);
        $this->payment_driver = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string plan = 15;</code>
     * @return string
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Generated from protobuf field <code>string plan = 15;</code>
     * @param string $var
     * @return $this
     */
    public function setPlan($var)
    {
        GPBUtil::checkString($var, True);
        $this->plan = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string deleted_at = 16;</code>
     * @return string
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    /**
     * Generated from protobuf field <code>string deleted_at = 16;</code>
     * @param string $var
     * @return $this
     */
    public function setDeletedAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->deleted_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string created_at = 17;</code>
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Generated from protobuf field <code>string created_at = 17;</code>
     * @param string $var
     * @return $this
     */
    public function setCreatedAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->created_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string updated_at = 18;</code>
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Generated from protobuf field <code>string updated_at = 18;</code>
     * @param string $var
     * @return $this
     */
    public function setUpdatedAt($var)
    {
        GPBUtil::checkString($var, True);
        $this->updated_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.orders.services.User user = 19;</code>
     * @return \Orders\Services\User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    public function hasUser()
    {
        return isset($this->user);
    }

    public function clearUser()
    {
        unset($this->user);
    }

    /**
     * Generated from protobuf field <code>.orders.services.User user = 19;</code>
     * @param \Orders\Services\User $var
     * @return $this
     */
    public function setUser($var)
    {
        GPBUtil::checkMessage($var, \Orders\Services\User::class);
        $this->user = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.orders.services.User to_user = 20;</code>
     * @return \Orders\Services\User|null
     */
    public function getToUser()
    {
        return $this->to_user;
    }

    public function hasToUser()
    {
        return isset($this->to_user);
    }

    public function clearToUser()
    {
        unset($this->to_user);
    }

    /**
     * Generated from protobuf field <code>.orders.services.User to_user = 20;</code>
     * @param \Orders\Services\User $var
     * @return $this
     */
    public function setToUser($var)
    {
        GPBUtil::checkMessage($var, \Orders\Services\User::class);
        $this->to_user = $var;

        return $this;
    }

}

