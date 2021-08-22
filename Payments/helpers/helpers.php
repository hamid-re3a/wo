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
                <p>We have received your order for the package {{package_name}}.</p>
                <p>Please pay the amount by logging in to the system or clicking the button below.
                </p>
                <p><a href="{{invoice_link}}">Pay Now</a></p>
                <p>Your package will be activated after the payment is received in the system.<br/>
                    Note:</br>
                    A) This invoice will expire after {{expiry_date}}<br/>
                    B) The invoice updates every two hours<br/>
                    C) Package price doesn't include the Transaction Charges.<br/>
                    D) In case of partial payment, the customer should pay the due amount on the same link.
                    </p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables' => 'full_name,package_name,invoice_link,expiry_date',
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
