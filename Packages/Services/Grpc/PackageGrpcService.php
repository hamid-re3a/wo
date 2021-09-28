<?php


namespace Packages\Services\Grpc;

use Mix\Grpc\Context;
use Packages\Services\PackageService;

class PackageGrpcService implements \Packages\Services\Grpc\PackagesServiceInterface
{
    private $package_service;

    public function __construct()
    {
        $this->package_service = app(PackageService::class);
    }

    /**
     * @inheritDoc
     */
    public function packageById(Context $context, Id $id): Package
    {
        return $this->package_service->packageFullById($id);
    }

}