<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Kyc\Services\Grpc;

/**
 */
class KycServiceClient extends \Grpc\BaseStub {

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
     * @return \Kyc\Services\Grpc\Acknowledge
     */
    public function checkKYCStatus(\User\Services\Grpc\User $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Kyc.services.grpc.KycService/checkKYCStatus',
        $argument,
        ['\Kyc\Services\Grpc\Acknowledge', 'decode'],
        $metadata, $options);
    }

}
