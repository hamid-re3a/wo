<?php

namespace Payments\Mail\Payment\Wallet;

use Carbon\Carbon;
use Payments\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Payments\Services\Invoice;
use User\Models\User;

class EmailWalletInvoiceExpired extends Mailable implements SettingableMail
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

        $setting['body'] = str_replace('{{full_name}}',(is_null($this->user->full_name) || empty($this->user->full_name)) ? 'Unknown': $this->user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{invoice_no}}',(is_null($this->invoice->getTransactionId()) || empty($this->invoice->getTransactionId())) ? 'Unknown': $this->invoice->getTransactionId(),$setting['body']);
        $setting['body'] = str_replace('{{usd_amount}}', (is_null($this->invoice->getPfAmount()) || empty($this->invoice->getPfAmount())) ? 'Unknown' : $this->invoice->getPfAmount(), $setting['body']);
        $setting['body'] = str_replace('{{expiry_date}}',(is_null($this->invoice->getExpirationTime()) || empty($this->invoice->getExpirationTime())) ? 'Unknown': Carbon::createFromTimestamp($this->invoice->getExpirationTime())->toString(),$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        return getPaymentEmailSetting('WALLET_INVOICE_EXPIRED_EMAIL');
    }

}
