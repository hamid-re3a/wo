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
    ]
];
