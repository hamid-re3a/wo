<?php

namespace Payments\Mail\Payment;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Orders\Services\Id;
use Orders\Services\Order;
use Orders\Services\OrderService;
use Payments\Mail\SettingableMail;
use Payments\Services\Invoice;
use User\Services\User;

class EmailInvoicePaidPartial extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $user;
    public $invoice;
    public $order;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Invoice $invoice
     * @param Order $order
     */
    public function __construct(User $user, Invoice $invoice, Order $order)
    {
        $this->user = $user;
        $this->invoice = $invoice;
        $this->order = $order;
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

        $setting['body'] = str_replace('{{full_name}}', (is_null($this->getUserFullName()) || empty($this->getUserFullName())) ? 'Unknown' : $this->getUserFullName(), $setting['body']);
        $setting['body'] = str_replace('{{due_amount}}', (is_null($this->getInvoiceDueAmount()) || empty($this->getInvoiceDueAmount())) ? 'Unknown' : $this->getInvoiceDueAmount(), $setting['body']);
        //$setting['body'] = str_replace('{{invoice_expire_duration}}',(is_null($this->getInvoiceExpirationTime()) || empty($this->getInvoiceExpirationTime())) ? 'Unknown': $this->getInvoiceExpirationTime(),$setting['body']);
        $setting['body'] = str_replace('{{amount_in_usd}}', (is_null($this->getPartialInPf()) || empty($this->getPartialInPf())) ? 'Unknown' : $this->getPartialInPf(), $setting['body']);
        //$setting['body'] = str_replace('{{expiry_date}}',(is_null($this->invoice->getExpirationTime()) || empty($this->invoice->getExpirationTime())) ? 'Unknown': Carbon::createFromTimestamp($this->invoice->getExpirationTime())->toString(),$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html($setting['body']);
    }

    public function getSetting(): array
    {
        return getPaymentEmailSetting('INVOICE_PARTIAL_PAID_EMAIL');
    }

    private function getUserFullName()
    {
        return ucwords(strtolower($this->user->getFirstName() . ' ' . $this->user->getLastName()));
    }

    private function getPartialInPf()
    {
        return number_format(
            (($this->invoice->getPfAmount() / $this->invoice->getAmount()) * $this->invoice->getDueAmount())
            , 2, '.', '');
    }

    private function getInvoiceDueAmount()
    {
        return $this->invoice->getDueAmount() . ' ' . $this->order->getPaymentCurrency();
    }

    private function getInvoiceExpirationTime()
    {
        return Carbon::createFromTimestamp($this->invoice->getExpirationTime())->diffForHumans();
    }
}
