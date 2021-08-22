<?php

namespace App\Jobs\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use User\Services\UserService;

class UserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

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
