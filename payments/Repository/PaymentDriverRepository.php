<?php
namespace Payments\Repository;


use Payments\Models\PaymentDriver;
use \Payments\Services\PaymentDriver as PaymentDriverObject;

class PaymentDriverRepository
{
    protected $entity_name = PaymentDriver::class;

    public function create(PaymentDriverObject $payment_driver)
    {
        $entity = new $this->entity_name;
        $entity->name = $payment_driver->getName();
        $entity->is_active = $payment_driver->getIsActive();
        $entity->save();
        return $entity;
    }

    public function update(PaymentDriverObject $payment_driver)
    {
        $payment_driver_entity = new $this->entity_name;
        $payment_driver_find = $payment_driver_entity->whereId($payment_driver->getId())->first();
        $payment_driver_find->name = $payment_driver->getName() ? $payment_driver->getName(): $payment_driver_find->name;
        $payment_driver_find->is_active = $payment_driver->getIsActive() ?? $payment_driver_find->is_active;
        if (!empty($payment_driver_find->getDirty())) {
            $payment_driver_find->save();
        }
        return $payment_driver_find;
    }

    public function delete(PaymentDriverObject $payment_driver)
    {
        $payment_driver_entity = new $this->entity_name;
        $payment_driver_find = $payment_driver_entity->whereId($payment_driver->getId())->delete();
        return $payment_driver_find;
    }
}
