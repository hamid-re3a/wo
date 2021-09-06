<?php

CONST WALLET_EMAIL_CONTENTS = [
    'TRANSFER_FUNDS_SENDER' => [
        'is_active' => true,
        'subject'=>'You transferred funds',
        'from'=>'it@ridetothefuture.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>You have transferred funds</p>
                <p></p>
                <p>Amount : <b>{{amount}}</b></p>
                <p>Fee : <b>{{fee}}</b></p>
                <p>To user name: <b>{{receiver_name}}</b></p>
                <p>To user member id : <b>{{receiver_member_id}}</b></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,amount,fee,receiver_name,receiver_member_id',
        'variables_description'=>'full_name user full name, amount transfer amount,fee transfer fee,receiver_name to user full name,receiver_member_id to user member id',
        'type'=>'email',
    ],
    'TRANSFER_FUNDS_RECEIVER' => [
        'is_active' => true,
        'subject'=>'You received funds',
        'from'=>'it@ridetothefuture.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>You have received funds</p>
                <p></p>
                <p>Amount : <b>{{amount}}</b></p>
                <p>From user name: <b>{{sender_name}}</b></p>
                <p>From user member id : <b>{{sender_member_id}}</b></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,amount,sender_name,sender_member_id',
        'variables_description'=>'full_name user full name, amount transfer amount,sender_name from user full name,sender_member_id from user member id',
        'type'=>'email',
    ],
    'PAYMENT_REQUEST' => [
        'is_active' => true,
        'subject'=>'{{request_first_name}} sent you a payment request',
        'from'=>'it@ridetothefuture.com',
        'from_name'=>'Janex Support Team',
        'body'=><<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>{{request_full_name}}, has asked you for {{amount}} PF</p>
                <p></p>
                <p>You can transfer this amount by going to the deposit wallet of your {{app_name}} account.</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name,amount,request_full_name',
        'variables_description'=>'full_name user full name, amount payment request amount,request_full_name payment requester',
        'type'=>'email',
    ]


];
