<?php

namespace User\tests\Feature;

use App\Jobs\User\UserDataJob;
use User\Models\User;
use User\tests\UserTest;

class ConsumeDataUserUpdateTest extends UserTest
{
    /**
     * @test
     */
    public function update_exist_user_consume_change_rabbit()
    {
        $user_model = User::query()->first();
        $user = $user_model->getUserService();
        $data = serialize($user);
        UserDataJob::dispatch($data);
        $this->assertTrue(true);
    }

}
