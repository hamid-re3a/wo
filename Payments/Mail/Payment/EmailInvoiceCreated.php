<?php

namespace Payments\Mail\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Payments\Mail\SettingableMail;
use Payments\Services\Invoice;
use User\Services\User;

class EmailInvoiceCreated extends Mailable implements SettingableMail
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

        $setting['body'] = str_replace('{{full_name}}', (is_null($this->getUserFullName()) || empty($this->getUserFullName())) ? 'Unknown' : $this->getUserFullName(), $setting['body']);
        $setting['body'] = str_replace('{{due_amount}}', (is_null($this->invoice->getInvoiceDueAmount()) || empty($this->invoice->getInvoiceDueAmount())) ? 'Unknown' : $this->invoice->getInvoiceDueAmount(), $setting['body']);
        $setting['body'] = str_replace('{{amount_in_usd}}', (is_null($this->invoice->getPartialInPf()) || empty($this->invoice->getPartialInPf())) ? 'Unknown' : $this->invoice->getPartialInPf(), $setting['body']);
        $setting['body'] = str_replace('{{payment_address}}', (is_null($this->invoice->getCheckoutLink()) || empty($this->invoice->getCheckoutLink())) ? 'Unknown' : $this->invoice->getCheckoutLink(), $setting['body']);
        $setting['body'] = str_replace('{{crypto}}', (is_null($this->invoice->getPaymentCurrency()) || empty($this->invoice->getPaymentCurrency())) ? 'Unknown' : $this->invoice->getPaymentCurrency(), $setting['body']);

        // $setting['body'] = str_replace('{{expiry_date}}',
        // (is_null($this->invoice->getExpirationTime()) || empty($this->invoice->getExpirationTime()))
        //         ? 'Unknown':
        //         Carbon::createFromTimestamp($this->invoice->getExpirationTime())->toString()
        //     ,$setting['body']);
        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html($setting['body']);
    }

    public function getSetting(): array
    {
        return getPaymentEmailSetting('INVOICE_CRATED_EMAIL');
    }

    private function getUserFullName()
    {
        return ucwords(strtolower($this->user->getFirstName() . ' ' . $this->user->getLastName()));
    }

    private function getCheckoutLink()
    {
        if (is_null($this->invoice->getCheckoutLink())) {
            return null;
        }

        return 'bitcoin:' . $this->invoice->getCheckoutLink() . '?amount=' . $this->invoice->getAmount();
    }

}
