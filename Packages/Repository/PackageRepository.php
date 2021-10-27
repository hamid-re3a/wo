<?php


namespace Packages\Repository;

use Illuminate\Support\Facades\DB;
use Packages\Models\Package;
use Packages\Services\Grpc\Id;
use Packages\Services\Grpc\Package as PackageData;

class PackageRepository
{
    protected $entity_name = Package::class;

    public function create(PackageData $package): Package
    {
        return (new $this->entity_name)->create([
            "name" => $package->getName(),
            "binary_percentage" => $package->getBinaryPercentage(),
            "category_id" => $package->getCategoryId(),
            "direct_percentage" => $package->getDirectPercentage(),
            "price" => $package->getPrice(),
            "roi_percentage" => $package->getRoiPercentage(),
            "short_name" => $package->getShortName(),
            "validity_in_days" => $package->getValidityInDays(),

        ]);
    }

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

    public function getById(Id $id): ?Package
    {
        $package_entity = new $this->entity_name;
        return $package_entity->find($id->getId());
    }

    public function getAll()
    {
        $package_entity = new $this->entity_name;
        return $package_entity->with('category', 'packageIndirectCommission')->get();
    }

    public function getCountAllPackageByMonth($from_month, $until_month)
    {
        $package_entity = new $this->entity_name;
        return $package_entity->select('short_name', 'category_id', 'created_at', DB::raw('count(id) as `count`'))->whereBetween('created_at', [$from_month, $until_month])->groupby('created_at', 'category_id', 'short_name')->get();
    }

    public function addOrEditPackageCommission($id, $level, $percentage)
    {
        /** @var  $package_entity Package */
        $package_entity = new $this->entity_name;
        $package_indirect_commission_entity = $package_entity->findOrFail($id)
            ->packageIndirectCommission()->firstOrCreate(['level' => $level]);
        $package_indirect_commission_entity->update(['percentage' => $percentage]);
        return $package_indirect_commission_entity;
    }

    public function deletePackageCommission($id, $level)
    {
        /** @var  $package_entity Package */
        $package_entity = new $this->entity_name;
        $package_indirect_commission_entity = $package_entity->findOrFail($id)
            ->packageIndirectCommission()->firstOrCreate(['level' => $level]);
        $package_indirect_commission_entity->delete();

    }

    public function deletePackage($id)
    {
        /** @var  $package_entity Package */
        $package_entity = new $this->entity_name;
        $package_entity->findOrFail($id)->delete();
    }

}
