<?php

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

if (!function_exists('example')) {

    function example()
    {

    }
}
