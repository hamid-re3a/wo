<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Payments\Services\Grpc;

/**
 */
class PaymentsServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Payments\Services\Grpc\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\Grpc\Invoice
     */
    public function getInvoiceById(\Payments\Services\Grpc\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.grpc.PaymentsService/getInvoiceById',
        $argument,
        ['\Payments\Services\Grpc\Invoice', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Payments\Services\Grpc\Invoice $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\Grpc\Invoice
     */
    public function pay(\Payments\Services\Grpc\Invoice $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.grpc.PaymentsService/pay',
        $argument,
        ['\Payments\Services\Grpc\Invoice', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Payments\Services\Grpc\EmptyObject $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\Grpc\PaymentCurrencies
     */
    public function getPaymentCurrencies(\Payments\Services\Grpc\EmptyObject $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.grpc.PaymentsService/getPaymentCurrencies',
        $argument,
        ['\Payments\Services\Grpc\PaymentCurrencies', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Payments\Services\Grpc\EmptyObject $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\Grpc\PaymentTypes
     */
    public function getPaymentTypes(\Payments\Services\Grpc\EmptyObject $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.grpc.PaymentsService/getPaymentTypes',
        $argument,
        ['\Payments\Services\Grpc\PaymentTypes', 'decode'],
        $metadata, $options);
    }

}
