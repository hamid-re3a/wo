<?php
// GENERATED CODE -- DO NOT EDIT!

namespace User\Services;

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
     * @param \User\Services\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \User\Services\User
     */
    public function getUserById(\User\Services\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/user.services.UserService/getUserById',
        $argument,
        ['\User\Services\User', 'decode'],
        $metadata, $options);
    }

}
