<?php

return [
    'payment_types' => [
        [
            'name'=>'gift code',
            'is_active'=>true,
        ],
        [
            'name'=>'gate way',
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
