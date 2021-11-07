<?php

return [
    'responses' => [
        'something-went-wrong' => 'Oops, Something went wrong, Try again please',

        'giftcode' => [
            'wrong-code' => 'Gift code is wrong',
            'expired' => 'Gift code is expired',
            'used' => 'Gift code is already used',
            'wrong-package' => 'Gift code is created for another package',
            'canceled' => 'Gift code is canceled',
            'giftcode-not-included-registration-fee' => 'Gift code doesn\'t include registration fee',
            'done' => 'Done',
        ],

        'wallet' => [
            'not-enough-balance' => 'Insufficient balance',
            'done' => 'Done .',
        ],
        'payment-service' => [
            'btc-pay-server-error' => 'We have an error on BTC Provider, Try again please',
            'gateway-error' => 'Oops, Something went wrong, Try again please',
            'pay-error' => 'Oops, Something went wrong, Try again please',
        ]
    ],
];

