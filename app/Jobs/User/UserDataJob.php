<?php

namespace App\Jobs\User;

use Google\Protobuf\Internal\GPBUtil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Orders\Services\OrderService;
use Packages\Services\PackageService;
use User\Services\UserService;

class UserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @param $data
     * @param OrderService $order_service
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userDataUnSerialize = unserialize($this->data);
        app(UserService::class)->userUpdate($userDataUnSerialize);
        echo "event has been handle. the first name and last name of userData is:".$userDataUnSerialize->getUsername() ." ". $userDataUnSerialize->getLastName(). PHP_EOL;

    }
}
