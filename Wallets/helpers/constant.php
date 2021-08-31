<?php

const WALLET_SETTINGS = [
    'fix_transfer_fee' => [
        'value' => 10,
        'title' => 'Fix transfer amount',
        'description' => 'Fix transfer amount'
    ],
    'percentage_transfer_fee' => [
        'value' => 10,
        'title' => 'Transfer fee percentage',
        'description' => 'Transfer fee in percentage'
    ],
    'transaction_fee_calculation' => [
        'value' => 'fix',
        'title' => 'How calculate transaction fee',
        'description' => 'Select Fix or Percentage transaction fee'
    ],
    'minimum_transfer_fund_amount' => [
        'value' => 100,
        'title' => 'Minimum transfer amount',
        'description' => 'Minimum transfer amount'
    ],
    'maximum_transfer_fund_amount' => [
        'value' => 10000,
        'title' => 'Minimum transfer amount',
        'description' => 'Minimum transfer amount'
    ],
    'minimum_payment_request_amount' => [
        'value' => 100,
        'title' => 'Minimum payment request amount',
        'description' => 'Minimum payment request amount'
    ],
    'maximum_payment_request_amount' => [
        'value' => 10000,
        'title' => 'Minimum payment request amount',
        'description' => 'Minimum payment request amount'
    ],

];

