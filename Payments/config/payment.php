<?php

return [
    'btc-pay-server-domain' => env('BTC_PAY_SERVER_DOMAIN','https://btcpay214900.lndyn.com/'),
    'btc-pay-server-store-id' => env('BTC_PAY_SERVER_STORE_ID','7LeJqgcLrNHrC3ia8ABPev3oWviBAFwNggnDngkf9mYa'),
    'btc-pay-server-api-token' => env('BTC_PAY_SERVER_API_TOKEN','i1MplO0Y770MIYH1Hl7pZ7vk7PkYVEBMVEW7LEUorWr'),

    'invoice_user_cancel_status' => 'user_cancel',

    'payment_types' => [
        [
            'name'=>'giftcode',
            'is_active'=>true,
        ],
        [
            'name'=>'purchase',
            'is_active'=>true,
        ],
        [
            'name'=>'deposit',
            'is_active'=>true,
        ]

    ],
    'payment_currencies' => [
        [
            'name'=>'BTC',
            'is_active'=>true,
        ]
    ],
    'payment_drivers' => [
        [
            'name'=>'btc-pay-server',
            'is_active'=>true,
        ]
    ],

];
