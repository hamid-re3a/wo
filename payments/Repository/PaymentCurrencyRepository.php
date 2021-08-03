<?php
namespace Payments\Repository;


use Packages\Models\Package;
use Payments\Models\PaymentCurrency;
use Payments\Models\PaymentType;

class PaymentCurrencyRepository
{
    protected $entity_name = PaymentCurrency::class;

    public function getAll()
    {
        return $this->entity_name::with("paymentDriver")->get();
    }
}
