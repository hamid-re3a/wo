<?php
// GENERATED CODE -- DO NOT EDIT!

namespace User\Services\Grpc;

/**
 */
class UserServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \User\Services\Grpc\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \User\Services\Grpc\User
     */
    public function getUserById(\User\Services\Grpc\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/user.services.grpc.UserService/getUserById',
        $argument,
        ['\User\Services\Grpc\User', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \User\Services\Grpc\User
     */
    public function getUserByMemberId(\User\Services\Grpc\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/user.services.grpc.UserService/getUserByMemberId',
        $argument,
        ['\User\Services\Grpc\User', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\WalletRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \User\Services\Grpc\WalletInfo
     */
    public function getUserWalletInfo(\User\Services\Grpc\WalletRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/user.services.grpc.UserService/getUserWalletInfo',
        $argument,
        ['\User\Services\Grpc\WalletInfo', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\UserTransactionPassword $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \User\Services\Grpc\Acknowledge
     */
    public function checkTransactionPassword(\User\Services\Grpc\UserTransactionPassword $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/user.services.grpc.UserService/checkTransactionPassword',
        $argument,
        ['\User\Services\Grpc\Acknowledge', 'decode'],
        $metadata, $options);
    }

}
