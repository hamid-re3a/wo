<?php

namespace App\Console\Commands;

use App\Jobs\User\RequestGetAllUserDataJob;
use App\Jobs\User\UserDataJob;
use Illuminate\Console\Command;
use Payments\Models\PaymentType;
use User\Services\UserUpdate;

class FireEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $user_update = new UserUpdate();
        $user_update->setQueueName('testseceound');
        $data_serialize = serialize($user_update);
        RequestGetAllUserDataJob::dispatch($data_serialize)->onConnection("rabbit")->onQueue("test");
        echo "request get all user data". PHP_EOL;
        /*        $entity = new PaymentType();
                $entity->name = "Dariush";
                $entity->is_active = false;
                $entity->save();
                $data = $entity->toArray();
                UserDataJob::dispatch($data)->onConnection('subscriptions');*/
    }
}
