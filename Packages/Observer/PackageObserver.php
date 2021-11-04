<?php

namespace Packages\Observer;

use Packages\Models\Package;
use Packages\Models\PackageHistory;
use Packages\Services\Grpc\Package as PackageService;

class PackageObserver
{

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

    public function log(Package $model)
    {
        if(auth()->check()) {
            PackageHistory::query()->create(array_merge($model->toArray(),[
                'package_id' => $model->id,
                'actor_id' => request()->header('X-user-id')
            ]));
        }
    }
}
