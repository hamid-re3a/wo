<?php


namespace Packages\Repository;

use Packages\Models\Package;
use Packages\Services\Id;
use Packages\Services\Package as PackageData;

class PackageRepository
{
    protected $entity_name = Package::class;

    public function edit(Id $id, PackageData $package): Package
    {
        $package_entity = new $this->entity_name;
        $package_find = $package_entity->whereId($id->getId())->first();
        $package_find->name = $package->getName() ?? $package_find->name;
        $package_find->binary_percentage = $package->getBinaryPercentage() ?? $package_find->binary_percentage;
        $package_find->category_id = $package->getCategoryId() ?? $package_find->category_id;
        $package_find->direct_percentage = $package->getDirectPercentage() ?? $package_find->direct_percentage;
        $package_find->price = $package->getPrice() ?? $package_find->price;
        $package_find->roi_percentage = $package->getRoiPercentage() ?? $package_find->roi_percentage;
        $package_find->short_name = $package->getShortName() ?? $package_find->short_name;
        $package_find->validity_in_days = $package->getValidityInDays() ?? $package_find->validity_in_days;
        if (!empty($package_find->getDirty())) {
            $package_find->save();
        }
        return $package_find;
    }

    public function getById(Id $id): Package
    {
        $package_entity = new $this->entity_name;
        return $package_entity->find($id->getId());
    }

    /**
     */
    public function getAll()
    {
        $package_entity = new $this->entity_name;
        return $package_entity->with('category','packageIndirectCommission')->get();
    }

}
