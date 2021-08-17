<?php

return [
    'btc-pay-server-domain' => 'https://staging-btcpayserver.janex.org/',
    'btc-pay-server-store-id' => '5ERagatYmuiBgRXVeHpNknuML8GbQFpD1LjeKMPrXEh7',
    'btc-pay-server-api-token' => 'token VnWQCkYob7Q0V0nd0M7GBG7oDoYLb7ilbDyH0YCBMkl',

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
