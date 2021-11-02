<?php


namespace Packages\Repository;

use Packages\Models\PackageHistory;
use Packages\Services\Grpc\Package;

class PackageHistoryRepository
{
    protected $entity_name = PackageHistory::class;

    public function create(Package $package) : PackageHistory
    {
        $package_history_entity = new $this->entity_name;
        $package_history_entity->legacy_id = $package->getId();
        $package_history_entity->name = $package->getName();
        $package_history_entity->binary_percentage = $package->getBinaryPercentage();
        $package_history_entity->category_id = $package->getCategoryId();
        $package_history_entity->direct_percentage = $package->getDirectPercentage();
        $package_history_entity->price = $package->getPrice();
        $package_history_entity->roi_percentage = $package->getRoiPercentage();
        $package_history_entity->short_name = $package->getShortName();
        $package_history_entity->validity_in_days = $package->getValidityInDays();
        $package_history_entity->save();
        return $package_history_entity;
    }
}
