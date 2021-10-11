<?php

namespace Payments\Mail\Payment\Wallet;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Payments\Mail\SettingableMail;
use Payments\Models\Invoice;
use User\Models\User;

class EmailWalletInvoicePaidPartial extends Mailable implements SettingableMail
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
    public function __construct(User $user, Invoice $invoice)
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

        $setting['body'] = str_replace('{{full_name}}', (is_null($this->user->full_name) || empty($this->user->full_name)) ? 'Unknown' : $this->user->full_name, $setting['body']);
        $setting['body'] = str_replace('{{due_amount}}', (is_null($this->getInvoiceDueAmount()) || empty($this->getInvoiceDueAmount())) ? 'Unknown' : $this->getInvoiceDueAmount(), $setting['body']);
        //$setting['body'] = str_replace('{{invoice_expire_duration}}',(is_null($this->getInvoiceExpirationTime()) || empty($this->getInvoiceExpirationTime())) ? 'Unknown': $this->getInvoiceExpirationTime(),$setting['body']);
        $setting['body'] = str_replace('{{amount_in_usd}}', (is_null($this->getPartialInPf()) || empty($this->getPartialInPf())) ? 'Unknown' : $this->getPartialInPf(), $setting['body']);
        $setting['body'] = str_replace('{{expiry_date}}',(is_null($this->invoice->expiration_time) || empty($this->invoice->expiration_time)) ? 'Unknown': Carbon::make($this->invoice->expiration_time)->toString(),$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html($setting['body']);
    }

    public function getSetting(): array
    {
        return getPaymentEmailSetting('WALLET_INVOICE_PARTIAL_PAID_EMAIL');
    }

    private function getPartialInPf()
    {
        return number_format(
            (($this->invoice->pf_amount / $this->invoice->amount) * $this->invoice->due_amount)
            , 2, '.', '');
    }

    private function getInvoiceDueAmount()
    {
        return $this->invoice->due_amount ;
//        return $this->invoice->getDueAmount() . ' ' . $this->order->getPaymentCurrency();
    }

    private function getInvoiceExpirationTime()
    {
        return Carbon::createFromTimestamp($this->invoice->expiration_time)->diffForHumans();
    }
}
