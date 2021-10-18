<?php

return [
    'length' => 6,
    'characters' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',
    'mask' => '***-***',
    'separator' => '_',
    'postfix' => 'team',
    'prefix' => 'janex',
    'use_postfix' => true,
    'use_prefix' => true,
    'has_expiration_date' => true,
    'giftcode_lifetime' => '10', // if has_expiration_date is true , giftcode_lifetime should be in days
    'include_cancellation_fee' => true,
    'cancellation_fee_type' => 'fixed',
    'cancellation_fee' => 1,
    'include_expiration_fee' => true,
    'expiration_fee_type' => 'fixed',
    'expiration_fee' => 1,
    'include_registration_fee' => true,
    'registration_fee' => 10
];
