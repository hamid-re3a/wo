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
     * @param \Orders\Services\Grpc\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Orders\Services\Grpc\Order
     */
    public function OrderById(\Orders\Services\Grpc\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/orders.services.grpc.OrdersService/OrderById',
        $argument,
        ['\Orders\Services\Grpc\Order', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Orders\Services\Grpc\Order $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Orders\Services\Grpc\Order
     */
    public function updateOrder(\Orders\Services\Grpc\Order $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/orders.services.grpc.OrdersService/updateOrder',
        $argument,
        ['\Orders\Services\Grpc\Order', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\Grpc\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Orders\Services\Grpc\Order
     */
    public function hasValidPackage(\User\Services\Grpc\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/orders.services.grpc.OrdersService/hasValidPackage',
        $argument,
        ['\Orders\Services\Grpc\Order', 'decode'],
        $metadata, $options);
    }

}
