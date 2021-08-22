<?php


namespace Packages\Services;

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
    public function packageById(Id $id): Package
    {
        $package = $this->package_repository->getById($id);
        if (is_null($package))
            return new Package();
        return $this->setPackage($package);
    }

    /**
     * @inheritDoc
     */
    public function packageFullById(Id $id): Package
    {
        $package = $this->package_repository->getById($id);
        if (is_null($package))
            return new Package();

        if(empty($package->validity_in_days))
            $package->validity_in_days = $package->category->package_validity_in_days;

        if(empty($package->roi_percentage))
            $package->roi_percentage = $package->category->roi_percentage;

        if(empty($package->direct_percentage))
            $package->direct_percentage = $package->category->direct_percentage;

        if(empty($package->binary_percentage))
            $package->binary_percentage = $package->category->binary_percentage;
        return $this->setPackage($package);
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
