<?php

CONST EMAIL_CONTENTS = [
    'GIFT_CODE_CREATED' => [
        'is_active' => true,
        'subject'=>'Gift code created is created',
        'from'=>'it@dreamcometrue.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>A new gift code is created. Below are the details of the code.</p>
                <p></p>
                <p>Code : <b>{{code}}</b></p>
                <p>Package name : <b>{{package_name}}</b></p>
                <p>Registration fee included : <b>{{registration_fee_included_or_not}}</b></p>
                <p>Total cost : <b>{{total_cost}}</b></p>
                <p>Expiration date : <b>{{expiration_date}}</b></p>
                <p>Please share the code with your friend for whom you created it.</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,code,package_name,expiration_date,total_cost',
        'variables_description'=>'full_name user full name, code giftcode code,package_name name of package,expiration_date giftcode expiration date,total_cost giftcode total cost',
        'type'=>'email',
    ],
    'GIFT_CODE_CANCELED' => [
        'is_active' => true,
        'subject'=>'Gift code is canceled',
        'from'=>'it@dreamcometrue.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your request for gift code ({{code}}) cancellation is processed successfully. We credited your deposit wallet and charged you for cancellation fee.</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,code,refund_amount',
        'variables_description'=>'full_name user full name, code giftcode code,refund_amount refunded amount to users wallet',
        'type'=>'email',
    ],
    'GIFTCODE_REDEEMED_CREATOR_EMAIL' => [
        'is_active' => true,
        'subject'=>'Giftcode is used',
        'from'=>'it@dreamcometrue.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your gift code ( {{code}} ) is used by your friend <b>{{redeem_user_full_name}}</b>. We have activated the {{package_name}} for him/her .</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,code,package_name,redeem_user_full_name,redeem_date',
        'variables_description'=>'full_name user full name, code giftcode code,package_name package name,redeem_user_full_name redeemer name, redeem_date redeemed time',
        'type'=>'email',
    ],
    'GIFTCODE_REDEEMED_REDEEMER_EMAIL' => [
        'is_active' => true,
        'subject'=>'You redeemed a gift code',
        'from'=>'it@dreamcometrue.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Thank you for the recent gift code <b>({{code}})</b> you have used. </p>
                <p>The gift code was provided by your friend,<b>{{creator_full_name}}</b> . </p>
                <p>Your package {{package_name}} will automatically activate.</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,code,package_name,creator_full_name,redeem_date',
        'variables_description'=>'full_name user full name, code giftcode code,package_name package name,creator_full_name creator name, redeem_date redeemed time',
        'type'=>'email',
    ],
    'GIFTCODE_WILL_EXPIRING_SOON_EMAIL' => [
        'is_active' => true,
        'subject'=>'Gift code is expiring soon',
        'from'=>'it@dreamcometrue.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your gift code ({{code}}) will expire on {{expiration_date}}. please use it as soon as possible </p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,code,expiration_date',
        'variables_description'=>'full_name user full name, code gift code code,expiration_date gift code expiration date',
        'type'=>'email',
    ],
    'GIFTCODE_EXPIRED_EMAIL' => [
        'is_active' => true,
        'subject'=>'Gift code is expired',
        'from'=>'it@dreamcometrue.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your gift code ({{code}}) is expired. we credited your deposit wallet and charged you for expiry fee .</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,code,expiration_date',
        'variables_description'=>'full_name user full name, code gift code code,expiration_date gift code expiration date',
        'type'=>'email',
    ],

];
