<?php

namespace User\tests\Feature;

use App\Jobs\User\UserDataJob;
use User\Services\User;
use User\tests\UserTest;

class ConsumeDataUserUpdateTest extends UserTest
{
    /**
     * @test
     */
    public function update_exist_user_consume_change_rabbit()
    {
        $user = new User();
        $user->setId(1);
        $user->setEmail("d@d.com");
        $user->setFirstName("RabbitNameTest1");
        $user->setLastName("RabbitFamilyTest1");
        $user->setUsername("Rabbit1");
        $user->setRole('test2,test4,test7');
        $data = serialize($user);
        UserDataJob::dispatch($data);
        $this->assertTrue(true);
    }

}
