<?php
namespace Payments\Repository;


use Payments\Models\PaymentCurrency;
use \Payments\Services\PaymentCurrency as PaymentCurrencyObject;

class PaymentCurrencyRepository
{
    protected $entity_name = PaymentCurrency::class;

    public function getAll()
    {
        return $this->entity_name::with("paymentDriver")->get();
    }

    public function create(PaymentCurrencyObject $payment_currency)
    {
        $entity = new $this->entity_name;
        $entity->name = $payment_currency->getName();
        $entity->is_active = $payment_currency->getIsActive();
        $entity->save();
        return $entity;
    }

    public function update(PaymentCurrencyObject $payment_currency)
    {
        $payment_currency_entity = new $this->entity_name;
        $payment_currency_find = $payment_currency_entity->whereId($payment_currency->getId())->first();
        $payment_currency_find->name = $payment_currency->getName() ? $payment_currency->getName(): $payment_currency_find->name;
        $payment_currency_find->is_active = $payment_currency->getIsActive() ? $payment_currency->getIsActive() : $payment_currency_find->is_active;
        if (!empty($payment_currency_find->getDirty())) {
            $payment_currency_find->save();
        }
        return $payment_currency_find;
    }

    public function delete(PaymentCurrencyObject $payment_currency)
    {
        $payment_currency_entity = new $this->entity_name;
        $payment_currency_find = $payment_currency_entity->whereId($payment_currency->getId())->delete();
        return $payment_currency_find;
    }
}
