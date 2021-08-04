<?php

return [
    'btc-pay-server-domain' => 'https://btcpay214900.lndyn.com/',
    'btc-pay-server-store-id' => '7LeJqgcLrNHrC3ia8ABPev3oWviBAFwNggnDngkf9mYa',
    'btc-pay-server-api-token' => 'i1MplO0Y770MIYH1Hl7pZ7vk7PkYVEBMVEW7LEUorWr',

    'payment_types' => [
        [
            'name'=>'gift-code',
            'is_active'=>true,
        ],
        [
            'name'=>'gate-way',
            'is_active'=>true,
        ],
        [
            'name'=>'wallet',
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
