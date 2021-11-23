<?php

const WALLET_WITHDRAW_COMMAND_UNDER_REVIEW = 1;
const WALLET_WITHDRAW_COMMAND_REJECT = 2;
const WALLET_WITHDRAW_COMMAND_PROCESS = 3;
const WALLET_WITHDRAW_COMMAND_POSTPONE = 4;
const WALLET_WITHDRAW_COMMAND_REVERT = 5;

const WALLET_WITHDRAW_COMMANDS = [
    WALLET_WITHDRAW_COMMAND_UNDER_REVIEW,
    WALLET_WITHDRAW_COMMAND_REJECT,
    WALLET_WITHDRAW_COMMAND_PROCESS,
    WALLET_WITHDRAW_COMMAND_POSTPONE
];

const WALLET_WITHDRAW_REQUEST_STATUS_UNDER_REVIEW_STRING = 'Under review';
const WALLET_WITHDRAW_REQUEST_STATUS_REJECT_STRING = 'Rejected';
const WALLET_WITHDRAW_REQUEST_STATUS_REVERT_STRING = 'Reverted';
const WALLET_WITHDRAW_REQUEST_STATUS_PROCESS_STRING = 'Processed';
const WALLET_WITHDRAW_REQUEST_STATUS_POSTPONE_STRING = 'Postponed';

const WALLET_WITHDRAW_REQUEST_STATUSES = [
    WALLET_WITHDRAW_REQUEST_STATUS_UNDER_REVIEW_STRING,
    WALLET_WITHDRAW_REQUEST_STATUS_REJECT_STRING,
    WALLET_WITHDRAW_REQUEST_STATUS_PROCESS_STRING,
    WALLET_WITHDRAW_REQUEST_STATUS_POSTPONE_STRING,
    WALLET_WITHDRAW_REQUEST_STATUS_REVERT_STRING,
];

const WALLET_ADMIN_DEPOSIT_ID = 1;
const WALLET_ADMIN_EARNING = 2;
const WALLET_ADMIN_CHARITY = 3;

const WALLET_ADMIN_WALLETS_IDS = [
  WALLET_ADMIN_DEPOSIT_ID,
  WALLET_ADMIN_EARNING,
  WALLET_ADMIN_CHARITY
];

const WALLET_NAME_DEPOSIT_WALLET = 'Deposit Wallet';
const WALLET_NAME_EARNING_WALLET = 'Earning Wallet';
const WALLET_NAME_JANEX_WALLET = 'Janex Wallet';
const WALLET_NAME_CHARITY_WALLET = 'Charity Wallet';

const WALLET_NAMES = [
    WALLET_NAME_DEPOSIT_WALLET,
    WALLET_NAME_EARNING_WALLET,
    WALLET_NAME_JANEX_WALLET,
];

const WALLET_SETTINGS = [
    'transfer_fee' => [
        'name' => 'transfer_fee',
        'value' => 10,
        'title' => 'Transfer fee',
        'description' => 'Transfer fee'
    ],
    'transaction_fee_calculation' => [
        'name' => 'transaction_fee_calculation',
        'value' => 'fix',
        'title' => 'How calculate transaction fee',
        'description' => 'Select Fix or Percentage transaction fee'
    ],
    'minimum_deposit_fund_amount' => [
        'name' => 'minimum_deposit_fund_amount',
        'value' => 100,
        'title' => 'Minimum deposit amount',
        'description' => 'Minimum deposit amount'
    ],
    'maximum_deposit_fund_amount' => [
        'name' => 'maximum_deposit_fund_amount',
        'value' => 10000,
        'title' => 'Minimum deposit amount',
        'description' => 'Minimum deposit amount'
    ],
    'minimum_transfer_fund_amount' => [
        'name' => 'minimum_transfer_fund_amount',
        'value' => 100,
        'title' => 'Minimum transfer amount',
        'description' => 'Minimum transfer amount'
    ],
    'maximum_transfer_fund_amount' => [
        'name' => 'maximum_transfer_fund_amount',
        'value' => 10000,
        'title' => 'Minimum transfer amount',
        'description' => 'Minimum transfer amount'
    ],
    'minimum_payment_request_amount' => [
        'name' => 'minimum_payment_request_amount',
        'value' => 100,
        'title' => 'Minimum payment request amount',
        'description' => 'Minimum payment request amount'
    ],
    'maximum_payment_request_amount' => [
        'name' => 'maximum_payment_request_amount',
        'value' => 10000,
        'title' => 'Minimum payment request amount',
        'description' => 'Minimum payment request amount'
    ],
    'minimum_transfer_from_earning_to_deposit_wallet_fund_amount' => [
        'name' => 'minimum_transfer_from_earning_to_deposit_wallet_fund_amount',
        'value' => 100,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'maximum_transfer_from_earning_to_deposit_wallet_fund_amount' => [
        'name' => 'maximum_transfer_from_earning_to_deposit_wallet_fund_amount',
        'value' => 10000,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'minimum_withdraw_request_from_earning_wallet_amount' => [
        'name' => 'minimum_withdraw_request_from_earning_wallet_amount',
        'value' => 1,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'maximum_withdraw_request_from_earning_wallet_amount' => [
        'name' => 'maximum_withdraw_request_from_earning_wallet_amount',
        'value' => 10000,
        'title' => 'Minimum allowed amount',
        'description' => 'Minimum allowed amount'
    ],
    'auto_payout_withdrawal_request_is_enable' => [
        'name' => 'auto_payout_withdrawal_request_is_enable',
        'value' => true,
        'title' => 'Auto payout for limited requests',
        'description' => 'Auto payout for limited requests'
    ],
    'maximum_auto_handle_withdrawals_payout' => [
        'name' => 'maximum_auto_handle_withdrawals_payout',
        'value' => 10000,
        'title' => 'Maximum USD amount',
        'description' => 'Maximum withdrawal amount to process automatically'
    ],
    'count_withdraw_requests_to_automatic_payout_process' => [
        'name' => 'count_withdraw_requests_to_automatic_payout_process',
        'value' => 5,
        'title' => 'Minimum withdraw requests for automatic payout.',
        'description' => 'Minimum withdraw requests for automatic payout'
    ],
    'payout_btc_fee_fixed_or_percentage' => [
        'name' => 'payout_btc_fee_fixed_or_percentage',
        'value' => 'percentage',
        'title' => 'Payout fee for BTC requests',
        'description' => 'Payout fee for BTC requests'
    ],
    'payout_btc_fee' => [
        'name' => 'payout_btc_fee',
        'value' => 5,
        'title' => 'Payout fee for BTC requests',
        'description' => 'Payout fee for BTC requests'
    ],
    'payout_janex_fee_fixed_or_percentage' => [
        'name' => 'payout_janex_fee_fixed_or_percentage',
        'value' => 'fixed',
        'title' => 'Payout fee for JANEX requests',
        'description' => 'Payout fee for JANEX requests'
    ],
    'payout_janex_fee' => [
        'name' => 'payout_janex_fee',
        'value' => 1,
        'title' => 'Payout fee for JANEX requests',
        'description' => 'Payout fee for JANEX requests'
    ],
    'withdrawal_request_is_enabled' => [
        'name' => 'withdrawal_request_is_enabled',
        'value' => 1,
        'title' => 'Enable Withdrawal',
        'description' => 'Withdrawal is enable or disable'
    ],

    'withdrawal_distribution_is_enabled' => [
        'name' => 'withdrawal_distribution_is_enabled',
        'value' => false,
        'title' => 'Distribution enabled',
        'description' => 'You can enable/disable Withdraw distribution feature'
    ],

    'withdrawal_distribution_in_btc' => [
        'name' => 'withdrawal_distribution_in_btc',
        'value' => 10,
        'title' => 'BTC distribution',
        'description' => 'BTC distribution'
    ],

    'withdrawal_distribution_in_janex' => [
        'name' => 'withdrawal_distribution_in_janex',
        'value' => 90,
        'title' => 'Janex distribution',
        'description' => 'Janex distribution'
    ],

    'charity_wallet_fee' => [
        'name' => 'charity_wallet_fee',
        'value' => 1,
        'title' => 'Charity fee',
        'description' => 'Charity fee'
    ],

    'charity_wallet_fixed_or_percentage' => [
        'name' => 'charity_wallet_fixed_or_percentage',
        'value' => 'percentage',
        'title' => 'Fixed calculation or percentage',
        'description' => 'Fixed calculation or percentage'
    ],

];

