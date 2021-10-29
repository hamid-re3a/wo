<?php

return [
    'responses' => [
        'something-went-wrong' => 'Oops, Something went wrong, Try again please.',
        'not-enough-balance' => 'Insufficient balance',
        'email-key-doesnt-exists' => "Wallet email content doesn't exists",
        'setting-key-doesnt-exists' => "Setting doesn't exists",
    ],
    'withdraw-profit-request' => [
        'withdrawal-requests-is-not-active' => 'Withdrawal is not active temporary, Try again later',
        'cant-find-wallet-address' => 'Please register your :name wallet address.',
        'btc-price-error' => 'We can\t fetch BTC live price, contact Developer team please.',
        'insufficient-bpb-wallet-balance' => 'Insufficient balance in our BTCPayServer wallet, Charge wallet for :amount',
        'external-resource-error' => 'We cant fetch data from :server',
        'not-enough-balance' => 'Insufficient balance, :amount + :fee',
        'withdraw_rank_limit' => 'You can\t withdraw more than :amount for today',
        'withdraw_distribution_limit' => 'You can\t withdraw :currency for today,Choose another one please.',
        'different-currency-payout' => 'All withdraw request should have same currency'
    ],

    'transfer_funds' => [
        'user-has-not-valid-package' => 'No package is associated with this account.',
    ]
];

