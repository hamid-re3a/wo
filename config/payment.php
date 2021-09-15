<?php

return [
    'btc-pay-server-domain' => 'https://testnet.demo.btcpayserver.org/',
    'btc-pay-server-store-id' => 'BbPfXmDoY2jhaCAkz2fxxYfnNgkRLCv2Agp1W1BNHUhE',
    'btc-pay-server-api-token' => 'token 2ce510aee96c5fa134622dd8adf95acb9f87c171',

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
    ]
];
