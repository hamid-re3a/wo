<?php

namespace Giftcode\Jobs;

use Giftcode\Mail\SettingableMail;
use Giftcode\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Packages\Services\PackageService;

class UpdatePackages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     * @var $packageService PackageService
     */
    private $packageService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = env('DEFAULT_QUEUE_NAME','default');
        $this->packageService = app(PackageService::class);;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->packageService->getPackages() AS $package) {
            $package = $this->getPackage($package['id']);
            Package::query()->firstOrCreate([
                'id' => $package->getId()
            ])->update([
                'name' => $package->getName(),
                'short_name' => $package->getShortName(),
                'validity_in_days' => $package->getValidityInDays(),
                'price' => $package->getPrice()
            ]);

        };
    }

    private function getPackage($id)
    {
        $request = new \Packages\Services\Grpc\Id();
        $request->setId($id);
        return $this->packageService->packageFullById($request);
    }
}
