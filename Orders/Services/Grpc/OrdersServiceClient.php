<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Orders\Services\Grpc;

/**
 */
class OrdersServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Orders\Services\Grpc\Order $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Orders\Services\Grpc\Acknowledge
     */
    public function sponsorPackage(\Orders\Services\Grpc\Order $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/orders.services.grpc.OrdersService/sponsorPackage',
        $argument,
        ['\Orders\Services\Grpc\Acknowledge', 'decode'],
        $metadata, $options);
    }

}
