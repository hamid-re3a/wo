<?php

use Illuminate\Http\Request;
use Orders\Models\OrderUser;
const ORDER_PLAN_DEFAULT = 'ORDER_PLAN_DEFAULT';
const ORDER_PLANS = [
    ORDER_PLAN_DEFAULT
];


const ORDER_PAYMENT_TYPE_GIFT_CODE = 'ORDER_PAYMENT_TYPE_GIFT_CODE';
const ORDER_PAYMENT_TYPE_GATEWAY = 'ORDER_PAYMENT_TYPE_GATEWAY';
const ORDER_PAYMENT_TYPE_WALLET = 'ORDER_PAYMENT_TYPE_WALLET';
const ORDER_PAYMENT_TYPES = [
    ORDER_PAYMENT_TYPE_GIFT_CODE,
    ORDER_PAYMENT_TYPE_GATEWAY,
    ORDER_PAYMENT_TYPE_WALLET
];

const ORDER_PAYMENT_DRIVER_BTC_PAY_SERVER = 'BTC_PAY_SERVER';
const ORDER_PAYMENT_DRIVERS = [
    ORDER_PAYMENT_DRIVER_BTC_PAY_SERVER
];



const ORDER_PAYMENT_CURRENCY_BTC = 'BTC';
const ORDER_PAYMENT_CURRENCIES = [
    ORDER_PAYMENT_CURRENCY_BTC
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
            $user->setUsername($request->header('X-user-email'));
            $user->setEmail($request->header('X-user-username'));


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
