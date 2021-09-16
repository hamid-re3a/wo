<?php

namespace Payments\Mail\Payment;

use Orders\Services\OrderService;
use Packages\Services\PackageService;
use User\Services\Grpc\User;
use Payments\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Payments\Models\Invoice;

class EmailInvoicePaidComplete extends Mailable implements SettingableMail
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
    public function __construct(User $user,Invoice $invoice)
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

        $setting['body'] = str_replace('{{full_name}}',(is_null($this->getUserFullName()) || empty($this->getUserFullName())) ? 'Unknown': $this->getUserFullName(),$setting['body']);
        $setting['body'] = str_replace('{{invoice_no}}',(is_null($this->invoice->transaction_id) || empty($this->invoice->transaction_id)) ? 'Unknown': $this->invoice->transaction_id,$setting['body']);
        $setting['body'] = str_replace('{{usd_amount}}', (is_null($this->invoice->pf_amount) || empty($this->invoice->pf_amount)) ? 'Unknown' : $this->invoice->pf_amount, $setting['body']);
        $setting['body'] = str_replace('{{package_name}}', (is_null($this->package->getName()) || empty($this->package->getName())) ? 'Unknown' : $this->package->getName(), $setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        return getPaymentEmailSetting('INVOICE_COMPLETE_PAID_EMAIL');
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
