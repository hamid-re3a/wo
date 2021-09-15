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

}
