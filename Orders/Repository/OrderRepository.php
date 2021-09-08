<?php

namespace Orders\Repository;

use Orders\Models\Order;

class OrderRepository
{
    protected $entity_name = Order::class;

    public function getCountSubscriptions()
    {
        $order = new $this->entity_name;
        $order_package_is_paid = $order->where([["is_paid_at", "!=", null], ["is_resolved_at", "!=", null]])->get();
        return count($order_package_is_paid);
    }


}
