<?php


namespace Packages\Services;

use Mix\Grpc\Context;
use Packages\Repository\PackageRepository;

class PackageService implements PackagesServiceInterface
{
    private $package_repository;

    public function __construct(PackageRepository $package_repository)
    {
        $this->package_repository = $package_repository;
    }

    /**
     * @inheritDoc
     */
    public function packageById(Context $context, Id $id): Package
    {
        return new Package();
        $packeage = $this->package_repository->getById($id);
        if (is_null($packeage))
            return new Package();
        return $this->setPackage($packeage);
    }





    /**
     * @param $packeage
     * @return Package
     */
    private function setPackage($packeage): Package
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

    public function editPackage(Id $id, Package $package): \Packages\Models\Package
    {
        return $this->package_repository->edit($id, $package);
    }

    public function getPackages()
    {
        return $this->package_repository->getAll();
    }
}
