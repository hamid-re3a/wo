<?php

namespace Packages\Observer;

use Packages\Models\Package;
use Packages\Services\PackageHistoryService;
use Packages\Services\Package as PackageService;

class PackageHistoryObserver
{
    private $packageHistoryService;

    public function __construct(PackageHistoryService $packageHistoryService)
    {
        $this->packageHistoryService = $packageHistoryService;
    }

    /**
     * Handle the Package "created" event.
     *
     * @param Package $package
     * @return void
     */
    public function created(Package $package)
    {
        $this->log($package);
    }

    /**
     * Handle the Package "updated" event.
     *
     * @param Package $package
     * @return void
     */
    public function updated(Package $package)
    {
        $this->log($package);
    }

    public function log($model)
    {

        $package = new PackageService();
        $package->setId($model->id);
        $package->setName($model->name);
        $package->setShortName($model->short_name);
        $package->setValidityInDays($model->validity_in_days);
        $package->setPrice($model->price);
        $package->setRoiPercentage($model->roi_percentage);
        $package->setDirectPercentage($model->direct_percentage);
        $package->setBinaryPercentage($model->binary_percentage);
        $package->setCategoryId($model->category_id);
        if (!empty($model->getDirty())) {
            $this->packageHistoryService->createPackageHistory($package);
        }
    }
}
