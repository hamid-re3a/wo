<?php

use Illuminate\Support\Facades\DB;
use Payments\Models\EmailContentSetting;
use Payments\Models\Setting;
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

const REGISTRATION_FEE = '20';

/**
 *  main settings
 */
const SETTINGS = [
    'REGISTRATION_FEE' => [
        'value' => REGISTRATION_FEE,
        'description' => 'Start Order Registration Fee in dollars',
        'category' => 'Order',
    ],
];


/**
 * payment email content settings
 */

const PAYMENT_EMAIL_CONTENT_SETTINGS = [
    'INVOICE_PARTIAL_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Invoice partial paid',
        'from' => 'support@janex.com',
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
    'INVOICE_COMPLETE_PAID_EMAIL' => [
        'is_active' => true,
        'subject' => 'Invoice paid',
        'from' => 'support@janex.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>We find out that you have paid of the invoice .</p>
                <p>Thank your for completing your payment.</p>
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
        'from' => 'support@janex.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>The invoice {{invoice_number}} has expired in {{expiry_date}}.</p>
                <p>Please login to the system to place a new order.</p>
                <p></p>
                <p>Cheers!</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,invoice_number,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
    'INVOICE_CRATED_EMAIL' => [
        'is_active' => true,
        'subject' => 'Invoice created',
        'from' => 'support@janex.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>We received your order and created an invoice for you. Please pay the invoice by either logging into the system or copy the below payment address to your crypto wallet.</p>
                <p>Due Amount: {{usd_amount}}&nbsp;<strong>&asymp;&nbsp;</strong> {{due_amount}}</p>
                <p>Payment address:&nbsp;{{payment_address}}</p>
                <p>Your package will be activated after the full payment is received in the system.</p>
                <p>&nbsp;</p>
                <h2><strong>Note:</strong></h2>  
                <ol>
                    <li>
                    <p>Pay this invoice in {{crypto}}</p>
                    </li>
                    <li>
                    <p>This invoice will expire after {{expiry_date}}</p>
                    </li>
                    <li>
                    <p>This invoice updates every {{update_hours}} hours and the due amount may change</p>
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
                    <p>This is the conversion rate confirmation of the due amount at the moment of creating the invoice: <a href="https://blockchain.info/tobtc?value={{usd_amount}}&currency=USD&time={{current_time}}&textual=true&nosavecurrency=true" target="_blank">Click Here</a></p>
                    </li>
                </ol>
                <p>&nbsp;</p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,package_name,usd_amount,due_amount,crypto,payment_address,current_time,update_hours,expiry_date',
        'variables_description' => 'full_name user full name',
        'type' => 'email',
    ],
];
if (!function_exists('getSetting')) {
    function getSetting($key)
    {
        if (DB::table('settings')->exists()) {
            $key_db = Setting::query()->where('key', $key)->first();
            if ($key_db && !empty($key_db->value))
                return $key_db->value;
        }

        if (isset(SETTINGS[$key]) && isset(SETTINGS[$key]['value']))
            return SETTINGS[$key]['value'];

        throw new Exception(trans('payment.responses.main-key-settings-is-missing'));
    }
}
if (!function_exists('getPaymentEmailSetting')) {


    function getPaymentEmailSetting($key)
    {
        if (DB::table('email_content_settings')->exists()) {
            $setting = EmailContentSetting::query()->where('key', $key)->first();
            if ($setting && !empty($setting->value))
                return $setting->toArray();
        }

        if (isset(PAYMENT_EMAIL_CONTENT_SETTINGS[$key]))
            return PAYMENT_EMAIL_CONTENT_SETTINGS[$key];

        throw new Exception(trans('payment.responses.main-key-settings-is-missing'));
    }
}
