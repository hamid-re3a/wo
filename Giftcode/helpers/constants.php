<?php

//Settings
const GIFTCODE_SETTINGS = [
    'characters' => [
        'name' => 'characters',
        'value' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',
        'title' => 'Available characters',
        'description' => 'Gift code include this characters'
    ],
    'length' => [
        'name' => 'length',
        'value' => 6,
        'title' => 'Giftcode length',
        'description' => 'Giftcode total characters'
    ],
    'separator' => [
        'name' => 'separator',
        'value' => '-',
        'title' => 'separator',
        'description' => 'Separator for prefix and postfix'
    ],
    'prefix' => [
        'name' => 'prefix',
        'value' => 'janex',
        'title' => 'Prefix',
        'description' => 'Prefix string for giftcode'
    ],
    'use_prefix' => [
        'name' => 'use_prefix',
        'value' => false,
        'title' => 'Include prefix',
        'description' => 'Use prefix for giftcode or not'
    ],
    'postfix' => [
        'name' => 'postfix',
        'value' => 'team',
        'title' => 'Postfix',
        'description' => 'Postfix string for giftcode'
    ],
    'use_postfix' => [
        'name' => 'use_postfix',
        'value' => false,
        'title' => 'Include postfix',
        'description' => 'Use postfix for giftcode or not'
    ],
    'has_expiration_date' => [
        'name' => 'has_expiration_date',
        'value' => true,
        'title' => 'Has expiration date',
        'description' => 'Giftcode has expiration date or not'
    ],
    'giftcode_lifetime' => [
        'name' => 'giftcode_lifetime',
        'value' => null,
        'title' => 'Giftcode life time',
        'description' => 'Giftcode life time'
    ],
    'include_cancellation_fee' => [
        'name' => 'include_cancellation_fee',
        'value' => true,
        'title' => 'Include cancellation fee',
        'description' => 'Include cancellation fee'
    ],
    'cancellation_fee_type' => [
        'name' => 'cancellation_fee_type',
        'value' => 'fixed',
        'title' => 'Cancellation fee fix or percentage',
        'description' => 'Cancellation fee fix or percentage'
    ],
    'cancellation_fee' => [
        'name' => 'cancellation_fee',
        'value' => 1,
        'title' => 'Cancellation fee',
        'description' => 'Cancellation fee for giftcode in percent'
    ],
    'include_expiration_fee' => [
        'name' => 'include_expiration_fee',
        'value' => true,
        'title' => 'Include expiration fee',
        'description' => 'Include expiration fee for giftcode or not'
    ],
    'expiration_fee_type' => [
        'name' => 'expiration_fee_type',
        'value' => 1,
        'title' => 'Expiration fee fixed or percentage',
        'description' => 'Expiration fee fixed or percentage'
    ],
    'expiration_fee' => [
        'name' => 'expiration_fee',
        'value' => 1,
        'title' => 'Expiration fee',
        'description' => 'Giftcode expiration fee in percent'
    ],
    'include_registration_fee' => [
        'name' => 'include_registration_fee',
        'value' => true,
        'title' => 'Include registration fee',
        'description' => 'Include registration fee for giftcode or not (If it is true, User can choose)'
    ],
    'registration_fee' => [
        'name' => 'registration_fee',
        'value' => 20,
        'title' => 'Registration fee',
        'description' => 'Registration fee will add in users payable amount'
    ]
];
