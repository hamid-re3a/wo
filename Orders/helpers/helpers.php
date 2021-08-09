<?php

use Illuminate\Http\Request;
use Orders\Models\OrderUser;
const ORDER_PLAN_UPGRADE = 'ORDER_PLAN_UPGRADE';
const ORDER_PLAN_START = 'ORDER_PLAN_START';
const ORDER_PLANS = [
    ORDER_PLAN_START,
    ORDER_PLAN_UPGRADE
];

if (!function_exists('user')) {

    function user(int $id ) : ?\Orders\Services\User
    {
        $user_db = OrderUser::query()->find($id);
        $user = new \Orders\Services\User();
        $user->setId((int)$user_db->id);
        $user->setFirstName($user_db->first_name);
        $user->setLastName($user_db->last_name);
        $user->setUsername($user_db->username);
        $user->setEmail($user_db->email);
        return $user;

    }
}
