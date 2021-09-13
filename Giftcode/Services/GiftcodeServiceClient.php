<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Giftcode\Services;

/**
 */
class GiftcodeServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Giftcode\Services\Giftcode $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Giftcode
     */
    public function createGiftcode(\Giftcode\Services\Giftcode $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/createGiftcode',
        $argument,
        ['\Giftcode\Services\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Giftcode $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Giftcode
     */
    public function redeemGiftcode(\Giftcode\Services\Giftcode $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/redeemGiftcode',
        $argument,
        ['\Giftcode\Services\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Giftcode $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Giftcode
     */
    public function cancelGiftcode(\Giftcode\Services\Giftcode $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/cancelGiftcode',
        $argument,
        ['\Giftcode\Services\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Giftcode
     */
    public function getGiftcodeById(\Giftcode\Services\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/getGiftcodeById',
        $argument,
        ['\Giftcode\Services\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Giftcode
     */
    public function getGiftcodeByUuid(\Giftcode\Services\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/getGiftcodeByUuid',
        $argument,
        ['\Giftcode\Services\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Count
     */
    public function getUserCreatedGiftcodesCount(\User\Services\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/getUserCreatedGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Count', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Count
     */
    public function getUserExpiredGiftcodesCount(\User\Services\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/getUserExpiredGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Count', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Count
     */
    public function getUserCanceledGiftcodesCount(\User\Services\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/getUserCanceledGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Count', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Count
     */
    public function getUserRedeemedGiftcodesCount(\User\Services\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.GiftcodeService/getUserRedeemedGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Count', 'decode'],
        $metadata, $options);
    }

}
