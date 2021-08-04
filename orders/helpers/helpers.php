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

    function user(Request $request) : ?\Orders\Services\User
    {
        if(
            $request->hasHeader('X-user-id') &&
            $request->hasHeader('X-user-first-name') &&
            $request->hasHeader('X-user-last-name') &&
            $request->hasHeader('X-user-email') &&
            $request->hasHeader('X-user-username')
        ){
            $user = new \Orders\Services\User();
            $user->setId((int)$request->header('X-user-id'));
            $user->setFirstName($request->header('X-user-first-name'));
            $user->setLastName($request->header('X-user-last-name'));
            $user->setUsername($request->header('X-user-username'));
            $user->setEmail($request->header('X-user-email'));


            $user_db = OrderUser::query()->firstOrCreate([
                'id' => $request->header('X-user-id')
            ]);
            $user_db->update([
                'first_name' => $request->header('X-user-first-name'),
                'last_name' => $request->header('X-user-last-name'),
                'email' => $request->header('X-user-email'),
                'username' => $request->header('X-user-username'),
            ]);

            return $user;
        }
        return null;
    }
}
