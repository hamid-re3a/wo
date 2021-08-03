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
}
