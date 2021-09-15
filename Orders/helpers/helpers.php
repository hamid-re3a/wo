<?php

use Illuminate\Http\Request;
const ORDER_PLAN_PURCHASE = 'ORDER_PLAN_PURCHASE';
const ORDER_PLAN_START = 'ORDER_PLAN_START';
const ORDER_PLANS = [
    ORDER_PLAN_START,
    ORDER_PLAN_PURCHASE
];

if (!function_exists('user')) {

    function user(int $id ) : ?\User\Services\Grpc\User
    {
        $user_db = \User\Models\User::query()->find($id);

        $user = new \User\Services\Grpc\User();
        $user->setId((int)$user_db->id);
        $user->setFirstName($user_db->first_name);
        $user->setLastName($user_db->last_name);
        $user->setUsername($user_db->username);
        $user->setEmail($user_db->email);
        return $user;

    }
}
