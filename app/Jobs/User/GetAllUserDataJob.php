<?php

namespace App\Jobs\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use User\Services\UserService;


class GetAllUserDataJob implements ShouldQueue
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
        $user_object_service = new \User\Services\User();
        $user_get_data_serialize = unserialize($this->data);
        foreach ($user_get_data_serialize as $item) {
            $block_type = isset($item['block_type']) && !empty($item['block_type'])? $item['block_type'] : null;
            $user_object_service->setId((int)$item['id']);
            $user_object_service->setFirstName($item['first_name']);
            $user_object_service->setLastName($item['last_name']);
            $user_object_service->setUsername($item['username']);
            $user_object_service->setEmail($item['email']);
            $user_object_service->setMemberId((int)$item['member_id']);
            $user_object_service->setBlockType($block_type);
            $user_object_service->setIsDeactivate($item['is_deactivate']);
            $user_object_service->setIsFreeze($item['is_freeze']);
            $user_object_service->setSponsorId((int)$item['sponsor_id']);

            $roles_array = [];
            foreach($item['roles'] AS $role) {
                $roles_array[] = $role['name'];
            }

            $user_object_service->setRole(implode(',',$roles_array));
            app(UserService::class)->userUpdate($user_object_service);
        }
//        echo "all user created".$this->data. PHP_EOL;

    }
}
