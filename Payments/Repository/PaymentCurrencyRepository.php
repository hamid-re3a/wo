<?php
namespace Payments\Repository;


use Payments\Models\PaymentCurrency;

class PaymentCurrencyRepository
{
    protected $entity_name = PaymentCurrency::class;

    public function getAll($filter)
    {

        $query = $this->entity_name::query();
        if(is_string($filter) && !empty($filter)){
//            $query->whereJsonContains('available_services', [$filter]);
            $query->where('available_services','like',"%$filter%");
        }
        return $query->get();
    }

    public function create($payment_currency)
    {
        $entity = new $this->entity_name;
        $entity->create([
            'name' => $payment_currency->name,
            'is_active' => $payment_currency->is_active,
            'available_services' => $payment_currency->available_services,
        ]);
        return $entity;
    }

    public function update($payment_currency)
    {
        $payment_currency_entity = new $this->entity_name;
        $payment_currency_find = $payment_currency_entity->firstOrCreate(['id' => $payment_currency->id]);

        $payment_currency_find->update([
            'name' => $payment_currency->name,
            'is_active' => $payment_currency->is_active,
            'available_services' => $payment_currency->available_services,
        ]);
        return $payment_currency_find;
    }

    public function delete($payment_currency)
    {
        $payment_currency_entity = new $this->entity_name;
        return  $payment_currency_entity->whereId($payment_currency->id)->delete();
    }
}
