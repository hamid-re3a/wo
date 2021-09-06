<?php

return [
    'btc-pay-server-domain' => 'https://staging-btcpayserver.janex.org/',
    'btc-pay-server-store-id' => '5ERagatYmuiBgRXVeHpNknuML8GbQFpD1LjeKMPrXEh7',
    'btc-pay-server-api-token' => 'token 346864af97312257f5292740876bc8c0fa67e8a4',

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
