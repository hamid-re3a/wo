<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: payments.proto

namespace Payments\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>payments.services.Payment</code>
 */
class Payment extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string name = 1;</code>
     */
    protected $name = '';
    /**
     * Generated from protobuf field <code>string short_name = 2;</code>
     */
    protected $short_name = '';
    /**
     * Generated from protobuf field <code>int32 validity_in_days = 3;</code>
     */
    protected $validity_in_days = 0;
    /**
     * Generated from protobuf field <code>double price = 4;</code>
     */
    protected $price = 0.0;
    /**
     * Generated from protobuf field <code>int32 roi_percentage = 5;</code>
     */
    protected $roi_percentage = 0;
    /**
     * Generated from protobuf field <code>int32 direct_percentage = 6;</code>
     */
    protected $direct_percentage = 0;
    /**
     * Generated from protobuf field <code>int32 binary_percentage = 7;</code>
     */
    protected $binary_percentage = 0;
    /**
     * Generated from protobuf field <code>int64 category_id = 8;</code>
     */
    protected $category_id = 0;
    /**
     * Generated from protobuf field <code>.payments.services.Invoice invoice = 9;</code>
     */
    protected $invoice = null;
    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp deleted_at = 10;</code>
     */
    protected $deleted_at = null;
    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp created_at = 11;</code>
     */
    protected $created_at = null;
    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp updated_at = 12;</code>
     */
    protected $updated_at = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *     @type string $short_name
     *     @type int $validity_in_days
     *     @type float $price
     *     @type int $roi_percentage
     *     @type int $direct_percentage
     *     @type int $binary_percentage
     *     @type int|string $category_id
     *     @type \Payments\Services\Invoice $invoice
     *     @type \Google\Protobuf\Timestamp $deleted_at
     *     @type \Google\Protobuf\Timestamp $created_at
     *     @type \Google\Protobuf\Timestamp $updated_at
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Payments::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string short_name = 2;</code>
     * @return string
     */
    public function getShortName()
    {
        return $this->short_name;
    }

    /**
     * Generated from protobuf field <code>string short_name = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setShortName($var)
    {
        GPBUtil::checkString($var, True);
        $this->short_name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 validity_in_days = 3;</code>
     * @return int
     */
    public function getValidityInDays()
    {
        return $this->validity_in_days;
    }

    /**
     * Generated from protobuf field <code>int32 validity_in_days = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setValidityInDays($var)
    {
        GPBUtil::checkInt32($var);
        $this->validity_in_days = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>double price = 4;</code>
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Generated from protobuf field <code>double price = 4;</code>
     * @param float $var
     * @return $this
     */
    public function setPrice($var)
    {
        GPBUtil::checkDouble($var);
        $this->price = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 roi_percentage = 5;</code>
     * @return int
     */
    public function getRoiPercentage()
    {
        return $this->roi_percentage;
    }

    /**
     * Generated from protobuf field <code>int32 roi_percentage = 5;</code>
     * @param int $var
     * @return $this
     */
    public function setRoiPercentage($var)
    {
        GPBUtil::checkInt32($var);
        $this->roi_percentage = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 direct_percentage = 6;</code>
     * @return int
     */
    public function getDirectPercentage()
    {
        return $this->direct_percentage;
    }

    /**
     * Generated from protobuf field <code>int32 direct_percentage = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setDirectPercentage($var)
    {
        GPBUtil::checkInt32($var);
        $this->direct_percentage = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 binary_percentage = 7;</code>
     * @return int
     */
    public function getBinaryPercentage()
    {
        return $this->binary_percentage;
    }

    /**
     * Generated from protobuf field <code>int32 binary_percentage = 7;</code>
     * @param int $var
     * @return $this
     */
    public function setBinaryPercentage($var)
    {
        GPBUtil::checkInt32($var);
        $this->binary_percentage = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int64 category_id = 8;</code>
     * @return int|string
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Generated from protobuf field <code>int64 category_id = 8;</code>
     * @param int|string $var
     * @return $this
     */
    public function setCategoryId($var)
    {
        GPBUtil::checkInt64($var);
        $this->category_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.payments.services.Invoice invoice = 9;</code>
     * @return \Payments\Services\Invoice|null
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    public function hasInvoice()
    {
        return isset($this->invoice);
    }

    public function clearInvoice()
    {
        unset($this->invoice);
    }

    /**
     * Generated from protobuf field <code>.payments.services.Invoice invoice = 9;</code>
     * @param \Payments\Services\Invoice $var
     * @return $this
     */
    public function setInvoice($var)
    {
        GPBUtil::checkMessage($var, \Payments\Services\Invoice::class);
        $this->invoice = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp deleted_at = 10;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    public function hasDeletedAt()
    {
        return isset($this->deleted_at);
    }

    public function clearDeletedAt()
    {
        unset($this->deleted_at);
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp deleted_at = 10;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setDeletedAt($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->deleted_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp created_at = 11;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function hasCreatedAt()
    {
        return isset($this->created_at);
    }

    public function clearCreatedAt()
    {
        unset($this->created_at);
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp created_at = 11;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreatedAt($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->created_at = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp updated_at = 12;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function hasUpdatedAt()
    {
        return isset($this->updated_at);
    }

    public function clearUpdatedAt()
    {
        unset($this->updated_at);
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Timestamp updated_at = 12;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setUpdatedAt($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->updated_at = $var;

        return $this;
    }

}

