<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Payments\Services;

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
     * @param \Payments\Services\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\Invoice
     */
    public function getInvoiceById(\Payments\Services\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.PaymentsService/getInvoiceById',
        $argument,
        ['\Payments\Services\Invoice', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Payments\Services\Invoice $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\Invoice
     */
    public function pay(\Payments\Services\Invoice $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.PaymentsService/pay',
        $argument,
        ['\Payments\Services\Invoice', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Payments\Services\EmptyObject $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\PaymentCurrencies
     */
    public function getPaymentCurrencies(\Payments\Services\EmptyObject $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.PaymentsService/getPaymentCurrencies',
        $argument,
        ['\Payments\Services\PaymentCurrencies', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Payments\Services\EmptyObject $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Payments\Services\PaymentTypes
     */
    public function getPaymentTypes(\Payments\Services\EmptyObject $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/payments.services.PaymentsService/getPaymentTypes',
        $argument,
        ['\Payments\Services\PaymentTypes', 'decode'],
        $metadata, $options);
    }

}
