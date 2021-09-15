<?php

namespace App\Jobs\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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
        $user_data_un_serialize = unserialize($this->data);
        app(UserService::class)->userUpdate($user_data_un_serialize);
//        Log::info("consume user data and updated by data:",[$this->data]);

//        echo "event has been handle. the first name and last name of userData is:".$user_data_un_serialize->getUsername() ." ". $user_data_un_serialize->getLastName(). PHP_EOL;

    }
}
