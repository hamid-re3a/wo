<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: wallets.proto

namespace Wallets\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>wallets.services.Transaction</code>
 */
class Transaction extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int64 id = 1;</code>
     */
    protected $id = 0;
    /**
     * Generated from protobuf field <code>double amount = 2;</code>
     */
    protected $amount = 0.0;
    /**
     * Generated from protobuf field <code>int64 from_user_id = 3;</code>
     */
    protected $from_user_id = 0;
    /**
     * Generated from protobuf field <code>int64 to_user_id = 4;</code>
     */
    protected $to_user_id = 0;
    /**
     * Generated from protobuf field <code>string description = 5;</code>
     */
    protected $description = '';
    /**
     * Generated from protobuf field <code>string from_wallet_name = 6;</code>
     */
    protected $from_wallet_name = '';
    /**
     * Generated from protobuf field <code>string to_wallet_name = 7;</code>
     */
    protected $to_wallet_name = '';
    /**
     * Generated from protobuf field <code>bool confiremd = 8;</code>
     */
    protected $confiremd = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $id
     *     @type float $amount
     *     @type int|string $from_user_id
     *     @type int|string $to_user_id
     *     @type string $description
     *     @type string $from_wallet_name
     *     @type string $to_wallet_name
     *     @type bool $confiremd
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Wallets::initOnce();
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
     * Generated from protobuf field <code>double amount = 2;</code>
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Generated from protobuf field <code>double amount = 2;</code>
     * @param float $var
     * @return $this
     */
    public function setAmount($var)
    {
        GPBUtil::checkDouble($var);
        $this->amount = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int64 from_user_id = 3;</code>
     * @return int|string
     */
    public function getFromUserId()
    {
        return $this->from_user_id;
    }

    /**
     * Generated from protobuf field <code>int64 from_user_id = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setFromUserId($var)
    {
        GPBUtil::checkInt64($var);
        $this->from_user_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int64 to_user_id = 4;</code>
     * @return int|string
     */
    public function getToUserId()
    {
        return $this->to_user_id;
    }

    /**
     * Generated from protobuf field <code>int64 to_user_id = 4;</code>
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
     * Generated from protobuf field <code>string description = 5;</code>
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Generated from protobuf field <code>string description = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setDescription($var)
    {
        GPBUtil::checkString($var, True);
        $this->description = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string from_wallet_name = 6;</code>
     * @return string
     */
    public function getFromWalletName()
    {
        return $this->from_wallet_name;
    }

    /**
     * Generated from protobuf field <code>string from_wallet_name = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setFromWalletName($var)
    {
        GPBUtil::checkString($var, True);
        $this->from_wallet_name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string to_wallet_name = 7;</code>
     * @return string
     */
    public function getToWalletName()
    {
        return $this->to_wallet_name;
    }

    /**
     * Generated from protobuf field <code>string to_wallet_name = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setToWalletName($var)
    {
        GPBUtil::checkString($var, True);
        $this->to_wallet_name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>bool confiremd = 8;</code>
     * @return bool
     */
    public function getConfiremd()
    {
        return $this->confiremd;
    }

    /**
     * Generated from protobuf field <code>bool confiremd = 8;</code>
     * @param bool $var
     * @return $this
     */
    public function setConfiremd($var)
    {
        GPBUtil::checkBool($var);
        $this->confiremd = $var;

        return $this;
    }

}

