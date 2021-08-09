<?php

namespace App\Jobs\User;

use Google\Protobuf\Internal\GPBUtil;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Orders\Services\User;

class UserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
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
        $user = new User();
        $ser = serialize($user);

        unserialize($ser);
        $data_final = json_encode($this->data['name']);
        echo "event has been handle. the name of userData is:".$data_final. PHP_EOL;

    }
}
