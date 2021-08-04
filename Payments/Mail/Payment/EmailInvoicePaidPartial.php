<?php

namespace Payments\Mail\Payment;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Orders\Services\User;
use Payments\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Payments\Services\Invoice;

class EmailInvoicePaidPartial extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $user;
    public $invoice;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Invoice $invoice
     */
    public function __construct(User $user,Invoice $invoice)
    {
        $this->user = $user;
        $this->invoice = $invoice;
    }


    /**
     * Build the message.
     *
     * @return $this
     * @throws \Exception
     */
    public function build()
    {
        $setting = $this->getSetting();

        $setting['body'] = str_replace('{{full_name}}',(is_null($this->getUserFullName()) || empty($this->getUserFullName())) ? 'Unknown': $this->getUserFullName(),$setting['body']);
        $setting['body'] = str_replace('{{due_amount}}',(is_null($this->getInvoiceDueAmount()) || empty($this->getInvoiceDueAmount())) ? 'Unknown': $this->getInvoiceDueAmount(),$setting['body']);
        $setting['body'] = str_replace('{{invoice_expire_duration}}',(is_null($this->getInvoiceExpirationTime()) || empty($this->getInvoiceExpirationTime())) ? 'Unknown': $this->getInvoiceExpirationTime(),$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        return getPaymentEmailSetting('INVOICE_PARTIAL_PAID_EMAIL');
    }


    private function getUserFullName()
    {
        return ucwords(strtolower($this->user->getFirstName() . ' ' . $this->user->getLastName()));
    }


    private function getInvoiceDueAmount()
    {
        return number_format((float)($this->invoice->getDueAmount()/($this->invoice->getDueAmount()+$this->invoice->getPaidAmount()) * $this->invoice->getAmount()),2, '.', '') . ' ' . $this->invoice->getOrder()->getPaymentCurrency();
    }

    private function getInvoiceExpirationTime()
    {
        return Carbon::createFromTimestamp($this->invoice->getExpirationTime())->diffForHumans();
    }
}
