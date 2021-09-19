<?php

CONST EMAIL_CONTENTS = [
    'GIFT_CODE_CREATED' => [
        'is_active' => true,
        'subject'=>'Gift code created successfully',
        'from'=>'it@ridetothefuture.com',
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
        'from'=>'it@ridetothefuture.com',
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
        'from'=>'it@ridetothefuture.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your gift code ( {{code}} ) is used by your friend {{redeem_user_full_name}}. We have activated the {{package_name}} for him/her .</p>
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
        'from'=>'it@ridetothefuture.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>You redeemed a giftcode</p>
                <p></p>
                <p>Code : <b>{{code}}</b></p>
                <p>Creator User : <b>{{creator_full_name}}</b></p>
                <p>Package name : <b>{{package_name}}</b></p>
                <p>Redeem date : <b>{{redeem_date}}</b></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,code,package_name,creator_full_name,redeem_date',
        'variables_description'=>'full_name user full name, code giftcode code,package_name package name,creator_full_name creator name, redeem_date redeemed time',
        'type'=>'email',
    ],

];
