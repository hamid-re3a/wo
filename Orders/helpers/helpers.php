<?php

use Illuminate\Http\Request;
const ORDER_PLAN_PURCHASE = 'ORDER_PLAN_PURCHASE';
const ORDER_PLAN_START = 'ORDER_PLAN_START';
const ORDER_PLAN_SPECIAL = 'ORDER_PLAN_SPECIAL';
const ORDER_PLAN_COMPANY = 'ORDER_PLAN_COMPANY';
const ORDER_PLANS = [
    ORDER_PLAN_START,
    ORDER_PLAN_PURCHASE,
    ORDER_PLAN_SPECIAL,
    ORDER_PLAN_COMPANY
];

if (!function_exists('getMLMGrpcClient')) {
    function getMLMGrpcClient()
    {
        return new \MLM\Services\Grpc\MLMServiceClient(env('MLM_GRPC_URL','staging-api-gateway.janex.org:9598'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
    }
}
