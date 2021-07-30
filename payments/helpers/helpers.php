<?php

use Payments\Models\EmailContentSetting;
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

/**
 *
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
                <p>We find out that you have paid part of the invoice .</p>
                <p>This invoice is valid only for {{invoice_expire_duration}} please complete your payment.</p>
                <p>Your invoice due amount is {{due_amount}} .</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name',
        'variables_description'=>'full_name user full name',
        'type'=>'email',
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
        'variables'=>'full_name',
        'variables_description'=>'full_name user full name',
        'type'=>'email',
    ],
    'INVOICE_EXPIRED_EMAIL' => [
        'is_active' => true,
        'subject' => 'Invoice expired',
        'from' => 'support@janex.com',
        'from_name' => 'Janex Support Team',
        'body' => <<<EOT
                <div>
                <p>Hello {{full_name}},</p>
                <p>Your previous invoice is expired now, please open new invoice with this <a href="">link </a> .</p>
                <p>Thank your for completing your payment.</p>
                <p></p>
                <p>Cheers,</p>
                <p>Janex Support Team</p>
                </div>
            EOT,
        'variables'=>'full_name',
        'variables_description'=>'full_name user full name',
        'type'=>'email',
    ],
];
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

        throw new Exception(trans('user.responses.main-key-settings-is-missing'));
    }
}
