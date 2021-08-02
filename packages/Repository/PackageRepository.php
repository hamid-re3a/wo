<?php


namespace Packages\Repository;

use Packages\Models\Package;
use Packages\Services\Id;
use Packages\Services\Package as PackageData;

class PackageRepository
{
    protected $entityName = Package::class;

    public function edit(Id $id, PackageData $package): Package
    {
        $packageEntity = new $this->entityName;
        $packageFind = $packageEntity->whereId($id->getId())->first();
        $packageFind->name = $package->getName() ?? $packageFind->name;
        $packageFind->binary_percentage = $package->getBinaryPercentage() ?? $packageFind->binary_percentage;
        $packageFind->category_id = $package->getCategoryId() ?? $packageFind->category_id;
        $packageFind->direct_percentage = $package->getDirectPercentage() ?? $packageFind->direct_percentage;
        $packageFind->price = $package->getPrice() ?? $packageFind->price;
        $packageFind->roi_percentage = $package->getRoiPercentage() ?? $packageFind->roi_percentage;
        $packageFind->short_name = $package->getShortName() ?? $packageFind->short_name;
        $packageFind->validity_in_days = $package->getValidityInDays() ?? $packageFind->validity_in_days;
        if (!empty($packageFind->getDirty())) {
            $packageFind->save();
        }
        return $packageFind;
    }

}
