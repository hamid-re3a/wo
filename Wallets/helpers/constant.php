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
    'minimum_deposit_fund_amount' => [
        'value' => 100,
        'title' => 'Minimum deposit amount',
        'description' => 'Minimum deposit amount'
    ],
    'maximum_deposit_fund_amount' => [
        'value' => 10000,
        'title' => 'Minimum deposit amount',
        'description' => 'Minimum deposit amount'
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
    'minimum_transfer_from_earning_to_deposit_wallet_fund_amount' => [
        'value' => 100,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'maximum_transfer_from_earning_to_deposit_wallet_fund_amount' => [
        'value' => 10000,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'minimum_withdraw_request_from_earning_wallet_amount' => [
        'value' => 1,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'maximum_withdraw_request_from_earning_wallet_amount' => [
        'value' => 10000,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'auto_payout_withdrawal_request_is_enable' => [
        'value' => true,
        'title' => 'Auto payout for limited requests',
        'description' => 'Auto payout for limited requests'
    ],
    'maximum_auto_handle_withdrawals_payout' => [
        'value' => 10000,
        'title' => 'Maximum USD amount',
        'description' => 'Maximum withdrawal amount to process automatically'
    ],
    'count_withdraw_requests_to_automatic_payout_process' => [
        'value' => 5,
        'title' => 'Minimum withdraw requests for automatic payout.',
        'description' => 'Minimum withdraw requests for automatic payout'
    ],
    'payout_btc_fee_fixed_or_percentage' => [
        'value' => 'percentage',
        'title' => 'Payout fee for BTC requests',
        'description' => 'Payout fee for BTC requests'
    ],
    'payout_btc_fee' => [
        'value' => 5,
        'title' => 'Payout fee for BTC requests',
        'description' => 'Payout fee for BTC requests'
    ],
    'payout_janex_fee_fixed_or_percentage' => [
        'value' => 'fixed',
        'title' => 'Payout fee for BTC requests',
        'description' => 'Payout fee for BTC requests'
    ],
    'payout_janex_fee' => [
        'value' => 1,
        'title' => 'Payout fee for BTC requests',
        'description' => 'Payout fee for BTC requests'
    ],
    'withdrawal_request_is_enabled' => [
        'value' => 1,
        'title' => 'Enable Withdrawal',
        'description' => 'Withdrawal is enable or disable'
    ],

];

