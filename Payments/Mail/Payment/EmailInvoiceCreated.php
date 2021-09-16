<?php

namespace Payments\Mail\Payment;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Orders\Services\OrderService;
use Packages\Services\PackageService;
use Payments\Mail\SettingableMail;
use Payments\Services\Grpc\Invoice;
use User\Services\Grpc\User;

class EmailInvoiceCreated extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $user;
    public $invoice;
    private $order;
    private $package;

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
        $this->order = $this->getOrder();
        $this->package = $this->getPackage();

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
        $setting['body'] = str_replace('{{invoice_no}}',(is_null($this->invoice->getTransactionId()) || empty($this->invoice->getTransactionId())) ? 'Unknown': $this->invoice->getTransactionId(),$setting['body']);
        $setting['body'] = str_replace('{{due_amount}}', (is_null($this->invoice->getAmount()) || empty($this->invoice->getAmount())) ? 'Unknown' : $this->invoice->getAmount(), $setting['body']);
        $setting['body'] = str_replace('{{usd_amount}}', (is_null($this->invoice->getPfAmount()) || empty($this->invoice->getPfAmount())) ? 'Unknown' : $this->invoice->getPfAmount(), $setting['body']);
        $setting['body'] = str_replace('{{usd_amount}}', (is_null($this->invoice->getPfAmount()) || empty($this->invoice->getPfAmount())) ? 'Unknown' : $this->invoice->getPfAmount(), $setting['body']);
        $setting['body'] = str_replace('{{payment_address}}', (is_null($this->invoice->getCheckoutLink()) || empty($this->invoice->getCheckoutLink())) ? 'Unknown' : $this->invoice->getCheckoutLink(), $setting['body']);
        $setting['body'] = str_replace('{{package_name}}', (is_null($this->package->getName()) || empty($this->package->getName())) ? 'Unknown' : $this->package->getName(), $setting['body']);
        $setting['body'] = str_replace('{{package_short_name}}', (is_null($this->package->getShortName()) || empty($this->package->getShortName())) ? 'Unknown' : $this->package->getShortName(), $setting['body']);
        $setting['body'] = str_replace('{{crypto}}', (is_null($this->invoice->getPaymentCurrency()) || empty($this->invoice->getPaymentCurrency())) ? 'Unknown' : $this->invoice->getPaymentCurrency(), $setting['body']);
        $setting['body'] = str_replace('{{current_time}}', round(microtime(true) * 1000), $setting['body']);
        $setting['body'] = str_replace('{{expiry_date}}', (is_null($this->invoice->getExpirationTime()) || empty($this->invoice->getExpirationTime())) ? 'Unknown' : Carbon::createFromTimestamp($this->invoice->getExpirationTime())->toString(), $setting['body']);
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

    private function getOrder()
    {
        $order_service = app(OrderService::class);
        $id_object = app(\Orders\Services\Grpc\Id::class);
        $id_object->setId($this->invoice->getPayableId());
        return $order_service->OrderById($id_object);

    }

    private function getPackage()
    {
        $package_service = app(PackageService::class);
        $id_object = app(\Packages\Services\Grpc\Id::class);
        $id_object->setId($this->order->getPackageId());
        return $package_service->packageById($id_object);
    }

}
