<?php
namespace Payments\Repository;


use Payments\Models\PaymentDriver;

class PaymentDriverRepository
{
    protected $entity_name = PaymentDriver::class;

    public function getAll()
    {
        return $this->entity_name::all();
    }

    public function create( $payment_driver)
    {
        $entity = new $this->entity_name;
        $entity->create([
            'name' => $payment_driver->name,
            'is_active' => $payment_driver->is_active,
        ]);
        return $entity;
    }

    public function update($payment_driver)
    {
        $payment_driver_entity = new $this->entity_name;
        $payment_currency_find = $payment_driver_entity->firstOrCreate(['id' => $payment_driver->id]);

        $payment_currency_find->update([
            'name' => $payment_driver->name,
            'is_active' => $payment_driver->is_active,
        ]);
        return $payment_currency_find;
    }

    public function delete($payment_driver)
    {
        $payment_driver_entity = new $this->entity_name;
        return $payment_driver_entity->whereId($payment_driver->id)->delete();
    }
}
