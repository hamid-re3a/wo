<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Packages\Services;

/**
 */
class PackagesServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Packages\Services\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Packages\Services\Package
     */
    public function packageById(\Packages\Services\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/packages.services.PackagesService/packageById',
        $argument,
        ['\Packages\Services\Package', 'decode'],
        $metadata, $options);
    }

}
