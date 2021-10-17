<?php

use Illuminate\Support\Facades\DB;
use Payments\Models\EmailContentSetting;
use Payments\Models\Setting;

const CURRENCY_SERVICE_PURCHASE = 'purchase';
const CURRENCY_SERVICE_WITHDRAW = 'withdraw';
const CURRENCY_SERVICE_DEPOSIT = 'deposit';
const CURRENCY_SERVICES = [
    CURRENCY_SERVICE_DEPOSIT,
    CURRENCY_SERVICE_WITHDRAW,
    CURRENCY_SERVICE_PURCHASE
];


const INVOICE_STATUS_NEW = 'New';
const INVOICE_STATUS_EXPIRED = 'Expired';
const INVOICE_STATUS_PAID = 'Paid';
const INVOICE_STATUS_CONFIRMED = 'Confirmed';
const INVOICE_STATUS_COMPLETE = 'Complete';
const INVOICE_STATUS_INVALID = 'Invalid';
const INVOICE_STATUSES = [
    INVOICE_STATUS_NEW,
    INVOICE_STATUS_EXPIRED,
    INVOICE_STATUS_PAID,
    INVOICE_STATUS_CONFIRMED,
    INVOICE_STATUS_COMPLETE,
    INVOICE_STATUS_INVALID,
];

const INVOICE_ADDITIONAL_STATUS_PARTIAL_PAID = 'paidPartial';
const INVOICE_ADDITIONAL_STATUS_NOT_PAID = 'Not paid';
const INVOICE_ADDITIONAL_STATUS_PAID = 'paid';
const INVOICE_ADDITIONAL_STATUS_MARKED = 'marked';
const INVOICE_ADDITIONAL_STATUS_PAID_LATE = 'paidLate';
const INVOICE_ADDITIONAL_STATUS_PAID_OVER = 'paidOver';
const INVOICE_ADDITIONAL_STATUSES = [
    INVOICE_ADDITIONAL_STATUS_PARTIAL_PAID,
    INVOICE_ADDITIONAL_STATUS_NOT_PAID,
    INVOICE_ADDITIONAL_STATUS_PAID,
    INVOICE_ADDITIONAL_STATUS_MARKED,
    INVOICE_ADDITIONAL_STATUS_PAID_LATE,
    INVOICE_ADDITIONAL_STATUS_PAID_OVER,
];

/**
 * Webhooks event types
 */

const WEBHOOK_EVENT_TYPE_INVOICE_CREATED = 'InvoiceCreated';
const WEBHOOK_EVENT_TYPE_INVOICE_RECEIVED_PAYMENT = 'InvoiceReceivedPayment';
const WEBHOOK_EVENT_TYPE_INVOICE_PAID_IN_FULL = 'InvoicePaidInFull';
const WEBHOOK_EVENT_TYPE_INVOICE_EXPIRED = 'InvoiceExpired';
const WEBHOOK_EVENT_TYPE_INVOICE_CONFIRMED = 'InvoiceConfirmed';
const WEBHOOK_EVENT_TYPE_INVOICE_INVALID = 'InvoiceInvalid';
const WEBHOOK_EVENT_TYPES = [
    WEBHOOK_EVENT_TYPE_INVOICE_CREATED,
    WEBHOOK_EVENT_TYPE_INVOICE_RECEIVED_PAYMENT,
    WEBHOOK_EVENT_TYPE_INVOICE_PAID_IN_FULL,
    WEBHOOK_EVENT_TYPE_INVOICE_EXPIRED,
    WEBHOOK_EVENT_TYPE_INVOICE_CONFIRMED,
    WEBHOOK_EVENT_TYPE_INVOICE_INVALID,
];

const REGISTRATION_FEE = '19.9';
const BF_TO_USD_RATE = 1;

/**
 *  main settings
 */
const SETTINGS = [
    'REGISTRATION_FEE' => [
        'value' => REGISTRATION_FEE,
        'description' => 'Start Order Registration Fee in dollars',
        'category' => 'Order',
    ],
    'BF_TO_USD_RATE' => [
        'value' => BF_TO_USD_RATE,
        'description' => 'coefficient of bf to usd',
        'category' => 'PF',
    ],
];

/**
 * payment email content settings
 */

const PAYMENT_EMAIL_CONTENT_SETTINGS = [
    'INVOICE_CREATED_EMAIL' => [
        'is_active' => true,
        'subject' => 'New Invoice',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>We are contacting you in regard to a new invoice #{{invoice_no}} that has been created on your account. Please pay the invoice by either logging into the system or copy the below payment address to your crypto wallet.</p>
                <p><strong>Due Amount:</strong> {{usd_amount}} USD<strong> &asymp; </strong> {{due_amount}} {{crypto}}</p>
                <p><strong>Payment address:</strong> {{payment_address}}</p>
                <p><strong>Package name:</strong> {{package_name}} ({{package_short_name}})</p>
                <h2><strong>Note:</strong></h2>
                <ol>
                    <li>
                    <p>Pay this invoice in {{crypto}}</p>
                    </li>
                    <li>
                    <p>This invoice will expire after {{expiry_date}}</p>
                    </li>
                    <li>
                    <p>The package price doesn&#39;t include the Transaction Fee.</p>
                    </li>
                    <li>
                    <p>In case of partial payment, the customer will receive a new email with the due amount and payment instructions. </p>
                    </li>
                    <li>
                    <p>If in case you received multiple invoices emails, please consider the latest one.</p>
                    </li>
                    <li>
                    <p>Here is the confirmation of the conversion rate of the due amount ({{usd_amount}} USD) to {{crypto}} at the moment of creating the invoice: <a href="https://blockchain.info/tobtc?value={{usd_amount}}&currency=USD&time={{current_time}}&textual=true&nosavecurrency=true" target="_blank">Click Here</a></p>
                    </li>
                    <li>
                    <p>If the due amount is more than the package price, then it includes the registration fee.</p>
                    </li>
                    <li>
                    <p>If you have any query regarding this invoice, please reach the support team and provide the invoice number</p>
                    </li>
                    <li>
                    <p>Your package will be activated after the full payment is received in the system.</p>
                    </li>
                </ol>
                <p>&nbsp;</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,package_name,usd_amount,due_amount,crypto,payment_address,current_time,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'INVOICE_PARTIAL_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Invoice partial paid',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>We noticed that you paid the invoice partially. Please pay the due amount {{amount_in_usd}} ≈ {{due_amount}} on time.</p>
                <p>This invoice will be expire at {{expiry_date}}</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,due_amount,amount_in_usd,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'INVOICE_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Payment Received',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hi {{full_name}}</p>
                <p>Thank you for the recent payment you have made to us for the sum of {{usd_amount}} USD. We hereby acknowledge receipt of payment which has been set against the invoice #{{invoice_no}}</p>
                <p>Your package ({{package_name}} ({{package_short_name}})) will automatically activate when we see enough confirmation on the block-chain network.</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'INVOICE_COMPLETE_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Payment Received',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hi {{full_name}}</p>
                <p>Thank you for the recent payment you have made to us for the sum of {{usd_amount}} USD. We hereby acknowledge receipt of payment which has been set against the invoice #{{invoice_no}}</p>
                <p>Your package ({{package_name}} ({{package_short_name}})) will automatically activate.</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'INVOICE_EXPIRED_EMAIL' => [
        'is_active' => true,
        'subject' => 'Invoice expired',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>The invoice #{{invoice_no}} for {{usd_amount}} was due at {{expiry_date}}. But we don't have any record of payment</p>
                <p>We appreciate if you can create a new invoice and set on time</p>
                <p></p>
                <p>Cheers!</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,invoice_no,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],


    'WALLET_INVOICE_CREATED_EMAIL' => [
        'is_active' => true,
        'subject' => 'Pay invoice on time',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>Thank you for creating the invoice for the sum of {{usd_amount}} USD. Please pay the invoice on time. As we receive your payment, we will directly credit your deposit wallet.</p>
                <p><strong>Due Amount:</strong> {{usd_amount}} USD<strong> &asymp; </strong> {{due_amount}} {{crypto}}</p>
                <p><strong>Payment address:</strong> {{payment_address}}</p>
                <p>As we receive payment in the system, we will deposit to your wallet .</p>
                <p>&nbsp;</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,package_name,usd_amount,due_amount,crypto,payment_address,current_time,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'WALLET_INVOICE_PARTIAL_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Deposit fund partial paid',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>We noticed that you paid the invoice partially. Please pay the due amount {{amount_in_usd}} ≈ {{due_amount}} on time.</p>
                <p>This invoice will be expire at {{expiry_date}}</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,due_amount,amount_in_usd,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'WALLET_INVOICE_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Deposit fund payment received',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hi {{full_name}}</p>
                <p>Thank you for the recent payment you have made to us for the sum of {{usd_amount}} USD. We hereby acknowledge receipt of payment which has been set against the invoice #{{invoice_no}}</p>
                <p>We will credit to your account as soon as we receive enough network confirmations.</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'WALLET_INVOICE_COMPLETE_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Deposit fund payment received',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hi {{full_name}}</p>
                <p>Thank you for the recent payment you have made to us for the sum of {{usd_amount}} USD. We hereby acknowledge receipt of payment which has been set against the invoice #{{invoice_no}}</p>
                <p>Your deposit wallet is credited. Your current balance is {{wallet_balance}} PF.</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'WALLET_INVOICE_EXPIRED_EMAIL' => [
        'is_active' => true,
        'subject' => 'Deposit fund invoice expired',
        'from' => 'it@ridetothefuture.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>The invoice #{{invoice_no}} for {{usd_amount}} was due at {{expiry_date}}. But we don't have any record of payment</p>
                <p>We appreciate if you can create a new invoice and set on time</p>
                <p></p>
                <p>Cheers!</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,invoice_no,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
];
if (!function_exists('getSetting')) {
    function getSetting($key)
    {
        if (DB::table('settings')->exists()) {
            $key_db = Setting::query()->where('key', $key)->first();
            if ($key_db && !empty($key_db->value)) {
                return $key_db->value;
            }

        }

        if (isset(SETTINGS[$key]) && isset(SETTINGS[$key]['value'])) {
            return SETTINGS[$key]['value'];
        }

        throw new Exception(trans('payment.responses.main-key-settings-is-missing'));
    }
}
if (!function_exists('getPaymentEmailSetting')) {

    function getPaymentEmailSetting($key)
    {
        $email = null;
        $setting = EmailContentSetting::query()->where('key', $key)->first();
        if ($setting && !empty($setting->value)) {
            $email = $setting->toArray();
        }

        if (isset(PAYMENT_EMAIL_CONTENT_SETTINGS[$key])) {
            $email =  PAYMENT_EMAIL_CONTENT_SETTINGS[$key];
        }

        if($email AND is_array($email)) {
            $email['from'] = env('MAIL_FROM', $email['from']);
            return $email;
        }

        throw new Exception(trans('payment.responses.main-key-settings-is-missing'));
    }
}


if (!function_exists('formatCurrencyNumber')) {

    function formatCurrencyNumber($value)
    {
        if (is_numeric($value))
            $value = number_format($value, 2);
//            $value = floatval(preg_replace('/[^\d.]/', '', number_format($value,2)));

        return $value;
    }
}

if (!function_exists('usdToPf')) {

    function usdToPf($value)
    {
        return (double)($value / (double)(getSetting('BF_TO_USD_RATE')));
    }
}

if (!function_exists('pfToUsd')) {

    function pfToUsd($value)
    {
        return (double)($value * ((double)getSetting('BF_TO_USD_RATE')));
    }
}
