<?php

CONST WALLET_EMAIL_CONTENTS = [
    'TRANSFER_FUNDS_SENDER' => [
        'key' => 'TRANSFER_FUNDS_SENDER',
        'is_active' => true,
        'subject' => 'You transferred funds',
        'from' => 'it@dreamcometrue.ai',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
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
        'variables' => 'full_name,amount,fee,receiver_name,receiver_member_id',
        'variables_description' => 'full_name user full name, amount transfer amount,fee transfer fee,receiver_name to user full name,receiver_member_id to user member id',
        'type' => 'email',
    ],
    'TRANSFER_FUNDS_RECEIVER' => [
        'key' => 'TRANSFER_FUNDS_RECEIVER',
        'is_active' => true,
        'subject' => 'You received funds',
        'from' => 'it@dreamcometrue.ai',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
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
        'variables' => 'full_name,amount,sender_name,sender_member_id',
        'variables_description' => 'full_name user full name, amount transfer amount,sender_name from user full name,sender_member_id from user member id',
        'type' => 'email',
    ],

    'PAYMENT_REQUEST' => [
        'key' => 'PAYMENT_REQUEST',
        'is_active' => true,
        'subject' => '{{request_first_name}} sent you a payment request',
        'from' => 'it@dreamcometrue.ai',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>{{request_full_name}} ({{request_member_id}}), has asked you for {{amount}} PF</p>
                <p></p>
                <p>You can transfer this amount by going to the deposit wallet of your {{app_name}} account.</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,amount,request_full_name',
        'variables_description' => 'full_name user full name, amount payment request amount,request_full_name payment requester',
        'type' => 'email',
    ],

    'WITHDRAW_REQUEST_SUBMITTED' => [
        'key' => 'WITHDRAW_REQUEST_SUBMITTED',
        'is_active' => true,
        'subject' => 'We received your withdrawal request',
        'from' => 'it@dreamcometrue.ai',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your withdrawal request has been submitted</p>
                <p></p>
                <p>Request ID : {{uuid}}</p>
                <p>Amount in PF : {{amount_in_pf}} PF</p>
                <p>Amount in BTC : {{amount_in_btc}} BTC</p>
                <p>Date : {{created_at}}</p>
                <p>Withdrawal transaction ID : {{withdraw_transaction_uuid}}</p>
                <p>We will inform you on any update for your request.</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,amount_in_pf,amount_in_btc,created_at,withdraw_transaction_uuid',
        'variables_description' => '
                full_name user full name,
                amount_in_pf withdraw request amount in pf,
                amount_in_btc withdraw request in BTC
                amount_in_btc withdraw request in BTC,
                created_at withdraw request date,
                withdraw_transaction_uuid withdraw transaction id in our system,

                ',
        'type' => 'email',
    ],
    'WITHDRAW_REQUEST_REJECTED' => [
        'key' => 'WITHDRAW_REQUEST_REJECTED',
        'is_active' => true,
        'subject' => 'Your withdrawal request rejected',
        'from' => 'it@dreamcometrue.ai',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your withdrawal request has been rejected</p>
                <p></p>
                <p>ID : {{uuid}}</p>
                <p>Amount in PF : {{amount_in_pf}}</p>
                <p>Amount in BTC : {{amount_in_btc}}</p>
                <p>Request date : {{created_at}}</p>
                <p>Update date : {{updated_at}}</p>
                <p>Rejection reason : {{act_reason}}</p>
                <p>Refund transaction ID : {{refund_transaction_uuid}}</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,amount_in_pf,amount_in_btc,created_at,refund_transaction_uuid,act_reason',
        'variables_description' => '
                full_name user full name,
                amount_in_pf withdraw request amount in pf,
                amount_in_btc withdraw request in BTC
                amount_in_btc withdraw request in BTC,
                created_at withdraw request date,
                act_reason Rejection reason,
                refund_transaction_uuid refund transaction id in our system,

                ',
        'type' => 'email',
    ],
    'WITHDRAW_REQUEST_PROCESSED' => [
        'key' => 'WITHDRAW_REQUEST_PROCESSED',
        'is_active' => true,
        'subject' => 'Your withdrawal request processed',
        'from' => 'it@dreamcometrue.ai',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your withdrawal request has been processed.</p>
                <p></p>
                <p>ID : {{uuid}}</p>
                <p>Amount in PF : {{amount_in_pf}}</p>
                <p>Amount in BTC : {{amount_in_btc}}</p>
                <p>Request Date : {{created_at}}</p>
                <p>Update Date : {{updated_at}}</p>
                <p>Withdrawal transaction ID : {{withdraw_transaction_uuid}}</p>
                <p>Network hash : {{network_hash}}</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,amount_in_pf,amount_in_btc,created_at,refund_transaction_uuid',
        'variables_description' => '
                full_name user full name,
                amount_in_pf withdraw request amount in pf,
                amount_in_btc withdraw request in BTC
                amount_in_btc withdraw request in BTC,
                created_at withdraw request date,
                refund_transaction_uuid refund transaction id in our system,

                ',
        'type' => 'email',
    ],
    'WITHDRAW_REQUEST_POSTPONED' => [
        'key' => 'WITHDRAW_REQUEST_POSTPONED',
        'is_active' => true,
        'subject' => 'Your withdrawal request postponed',
        'from' => 'it@dreamcometrue.ai',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello <b>{{full_name}}</b>,</p>
                <p>Your withdrawal request has been processed.</p>
                <p></p>
                <p>ID : {{uuid}}</p>
                <p>Amount in PF : {{amount_in_pf}}</p>
                <p>Amount in BTC : {{amount_in_btc}}</p>
                <p>Withdrawal transaction ID : {{withdraw_transaction_uuid}}</p>
                <p>Request Date : {{created_at}}</p>
                <p>Update Date : {{updated_at}}</p>
                <p>Postpone Date : {{postpone_to}}</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,amount_in_pf,amount_in_btc,created_at,refund_transaction_uuid',
        'variables_description' => '
                full_name user full name,
                amount_in_pf withdraw request amount in pf,
                amount_in_btc withdraw request in BTC
                amount_in_btc withdraw request in BTC,
                created_at withdraw request date,
                refund_transaction_uuid refund transaction id in our system,

                ',
        'type' => 'email',
    ],


];
