<?php

return [
    'phrases' => [
        'no_expiration_date' => 'No expiration date',
        'buy-a-giftcode' => 'Buy a giftcode'
    ],
    'responses' => [
        'something-went-wrong' => 'Oops, Something went wrong, Try again please.',
        'global-error' => 'An error occurred, Try again please.',
        'ok'=>'ok',
        'setting-key-doesnt-exists' => "setting key {:key} doesn't exists",
        'email-key-doesnt-exists' => "giftcode email {:key} content doesn't exists",
        'not-valid-giftcode-id' => 'Giftcode ID is not valid',

        'giftcode-is-canceled-and-user-cant-cancel' => 'You can not cancel a giftcode again.',
        'giftcode-is-used-and-user-cant-cancel' => 'You can not cancel a used giftcode.',
        'giftcode-is-expired-and-user-cant-cancel' => 'You can not cancel an expired giftcode.',

        'giftcode-canceled-amount-refunded' => 'Giftcode has been canceled and :amount deposited to your Deposit Wallet',

        'giftcode-is-canceled-and-user-cant-redeem' => 'You can not redeem a canceled giftcode.',
        'giftcode-is-used-and-user-cant-redeem' => 'You can not redeem a used giftcode.',
        'giftcode-is-expired-and-user-cant-redeem' => 'You can not redeem an expired giftcode.',
        'incorrect-transaction-password' => 'Your transaction password is incorrect.',

    ],
    'validation' => [
        'inefficient-account-balance' => 'Your account balance is lower than :amount',
        'wallet-withdrawal-error' => 'An error occurred,Try again please.',
    ]
];

