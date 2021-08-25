<?php

CONST EMAIL_CONTENTS = [
    'GIFT_CODE_CREATED' => [
        'is_active' => true,
        'subject'=>'Gift code created',
        'from'=>'support@janex.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your new giftcode has been created</p>
                <p></p>
                <p>Code : <b>{{code}}</b></p>
                <p>Package : <b>{{package_name}}</b></p>
                <p>Total cost : <b>{{total_cost}}</b></p>
                <p>Expiration date : <b>{{expiration_date}}</b></p>
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
        'subject'=>'Gift code canceled',
        'from'=>'support@janex.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your giftcode has been canceled</p>
                <p></p>
                <p>Code : <b>{{code}}</b></p>
                <p>Total refunded amount : <b>{{refund_amount}}</b></p>
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
        'subject'=>'Your Giftcode redeemed',
        'from'=>'support@janex.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your giftcode has been redeemed</p>
                <p></p>
                <p>Code : <b>{{code}}</b></p>
                <p>Package name : <b>{{package_name}}</b></p>
                <p>Redeem User : <b>{{redeem_user_full_name}}</b></p>
                <p>Redeem date : <b>{{redeem_date}}</b></p>
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
        'from'=>'support@janex.com',
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
