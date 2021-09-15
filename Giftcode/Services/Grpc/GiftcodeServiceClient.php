<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Giftcode\Services\Grpc;

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
     * @param \Giftcode\Services\Grpc\Giftcode $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Giftcode
     */
    public function createGiftcode(\Giftcode\Services\Grpc\Giftcode $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/createGiftcode',
        $argument,
        ['\Giftcode\Services\Grpc\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Grpc\Giftcode $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Giftcode
     */
    public function redeemGiftcode(\Giftcode\Services\Grpc\Giftcode $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/redeemGiftcode',
        $argument,
        ['\Giftcode\Services\Grpc\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Grpc\Giftcode $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Giftcode
     */
    public function cancelGiftcode(\Giftcode\Services\Grpc\Giftcode $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/cancelGiftcode',
        $argument,
        ['\Giftcode\Services\Grpc\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Grpc\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Giftcode
     */
    public function getGiftcodeById(\Giftcode\Services\Grpc\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/getGiftcodeById',
        $argument,
        ['\Giftcode\Services\Grpc\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Giftcode\Services\Grpc\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Giftcode
     */
    public function getGiftcodeByUuid(\Giftcode\Services\Grpc\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/getGiftcodeByUuid',
        $argument,
        ['\Giftcode\Services\Grpc\Giftcode', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Count
     */
    public function getUserCreatedGiftcodesCount(\User\Services\Grpc\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/getUserCreatedGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Grpc\Count', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Count
     */
    public function getUserExpiredGiftcodesCount(\User\Services\Grpc\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/getUserExpiredGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Grpc\Count', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Count
     */
    public function getUserCanceledGiftcodesCount(\User\Services\Grpc\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/getUserCanceledGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Grpc\Count', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Giftcode\Services\Grpc\Count
     */
    public function getUserRedeemedGiftcodesCount(\User\Services\Grpc\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/giftcode.services.grpc.GiftcodeService/getUserRedeemedGiftcodesCount',
        $argument,
        ['\Giftcode\Services\Grpc\Count', 'decode'],
        $metadata, $options);
    }

}
