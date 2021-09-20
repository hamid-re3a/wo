<?php
// GENERATED CODE -- DO NOT EDIT!

namespace MLM\Services\Grpc;

/**
 */
class MLMServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \User\Services\Grpc\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \MLM\Services\Grpc\Acknowledge
     */
    public function hasValidPackage(\User\Services\Grpc\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/MLM.services.grpc.MLMService/hasValidPackage',
        $argument,
        ['\MLM\Services\Grpc\Acknowledge', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Orders\Services\Grpc\Order $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \MLM\Services\Grpc\Acknowledge
     */
    public function simulateOrder(\Orders\Services\Grpc\Order $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/MLM.services.grpc.MLMService/simulateOrder',
        $argument,
        ['\MLM\Services\Grpc\Acknowledge', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Orders\Services\Grpc\Order $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \MLM\Services\Grpc\Acknowledge
     */
    public function submitOrder(\Orders\Services\Grpc\Order $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/MLM.services.grpc.MLMService/submitOrder',
        $argument,
        ['\MLM\Services\Grpc\Acknowledge', 'decode'],
        $metadata, $options);
    }

}
