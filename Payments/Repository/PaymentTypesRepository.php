<?php
namespace Payments\Repository;


use Payments\Models\PaymentType;

class PaymentTypesRepository
{
    protected $entity_name = PaymentType::class;

    public function getAll()
    {
        return $this->entity_name::all();
    }

    public function getAllActives()
    {
        return $this->entity_name::query()->where('is_active',true)->get();
    }

    public function create($payment_type)
    {
        $entity = new $this->entity_name;
        $entity->create([
            'name' => $payment_type->name,
            'is_active' => $payment_type->is_active,
        ]);
        return $entity;
    }

    public function update($payment_type)
    {
        $payment_type_entity = new $this->entity_name;
        $payment_type_find = $payment_type_entity->firstOrCreate(['id'=>$payment_type->id]);

        $payment_type_find->name = $payment_type->name ? $payment_type->name: $payment_type_find->name;
        $payment_type_find->is_active = $payment_type->is_active ?? $payment_type_find->is_active;
        if (!empty($payment_type_find->getDirty())) {
            $payment_type_find->save();
        }
        return $payment_type_find;
    }

    public function delete($payment_type)
    {
        $payment_type_entity = new $this->entity_name;
        return $payment_type_entity->whereId($payment_type->id)->delete();
    }
}
