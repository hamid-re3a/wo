<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Packages\Services\Grpc;

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
     * @param \Packages\Services\Grpc\Id $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Packages\Services\Grpc\Package
     */
    public function packageById(\Packages\Services\Grpc\Id $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/packages.services.grpc.PackagesService/packageById',
        $argument,
        ['\Packages\Services\Grpc\Package', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Packages\Services\Grpc\PackageCheck $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Packages\Services\Grpc\Acknowledge
     */
    public function packageIsInBiggestPackageCategory(\Packages\Services\Grpc\PackageCheck $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/packages.services.grpc.PackagesService/packageIsInBiggestPackageCategory',
        $argument,
        ['\Packages\Services\Grpc\Acknowledge', 'decode'],
        $metadata, $options);
    }

}
