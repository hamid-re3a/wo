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
        return $this->setPackageHistory($this->package_history_repository->create($package));
    }

    /**
     * @param $packeage
     * @return Package
     */
    private function setPackageHistory($packeage)
    {
        $response_package = new Package();
        $response_package->setId($packeage->id);
        $response_package->setName($packeage->name);
        $response_package->setShortName($packeage->short_name);
        $response_package->setValidityInDays((int)$packeage->validity_in_days);
        $response_package->setPrice((double)$packeage->price);
        $response_package->setRoiPercentage((int)$packeage->roi_percentage);
        $response_package->setDirectPercentage((int)$packeage->direct_percentage);
        $response_package->setBinaryPercentage((int)$packeage->binary_percentage);
        $response_package->setCategoryId((int)$packeage->category_id);
        return $response_package;
    }

}
