<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Orders\Services;

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
     * @param \Orders\Services\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Orders\Services\Order
     */
    public function OrderById(\Orders\Services\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/orders.services.OrdersService/OrderById',
        $argument,
        ['\Orders\Services\Order', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Orders\Services\Order $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Orders\Services\Order
     */
    public function updateOrder(\Orders\Services\Order $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/orders.services.OrdersService/updateOrder',
        $argument,
        ['\Orders\Services\Order', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \User\Services\User $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Orders\Services\Order
     */
    public function hasValidPackage(\User\Services\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/orders.services.OrdersService/hasValidPackage',
        $argument,
        ['\Orders\Services\Order', 'decode'],
        $metadata, $options);
    }

}
