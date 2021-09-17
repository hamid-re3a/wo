<?php


namespace Packages\Services;

use Mix\Grpc\Context;
use Packages\Services\Grpc\Id;
use Packages\Services\Grpc\Package;

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
        return $this->package_service->packageById($id);
    }

}
