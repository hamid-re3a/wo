<?php


namespace Packages\Services;

use Packages\Repository\PackageHistoryRepository;
use Packages\Services\Grpc\Package;

class PackageHistoryService
{
    private $package_history_repository;

    public function __construct(PackageHistoryRepository $package_history_repository)
    {
        $this->package_history_repository = $package_history_repository;
    }

    public function createPackageHistory($package): Package
    {
        return $this->package_history_repository->create($package)->getGrpcMessage();
    }

}
