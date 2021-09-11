<?php

namespace Orders\Repository;

use Orders\Models\Order;

class OrderRepository
{
    protected $entity_name = Order::class;

    public function getCountSubscriptions()
    {
        $order = new $this->entity_name;
        return $order_package_is_paid = $order->where([["is_paid_at", "!=", null], ["is_resolved_at", "!=", null]])->count();
    }


    public function getCountActivePackage()
    {
        $order = new $this->entity_name;

        return $order->where([["is_paid_at", "!=", null], ["is_resolved_at", "!=", null]])->whereRaw("CURRENT_TIMESTAMP < DATE_ADD(is_paid_at, INTERVAL validity_in_days DAY)")->count();
    }

    public function getCountDeactivatePackage()
    {
        $order = new $this->entity_name;

        return $order->where([["is_paid_at", "!=", null], ["is_resolved_at", "!=", null]])->whereRaw("CURRENT_TIMESTAMP >= DATE_ADD(is_paid_at, INTERVAL validity_in_days DAY)")->count();
    }


    public function getCountActivePackageByDate($from,$until)
    {
        $order = new $this->entity_name;

        return $order->where([["is_paid_at", "!=", null], ["is_resolved_at", "!=", null]])->whereRaw("CURRENT_TIMESTAMP < DATE_ADD(is_paid_at, INTERVAL validity_in_days DAY)")->whereBetween('created_at',[$from,$until])->get();
    }

    public function getCountDeactivatePackageByDate($from,$until)
    {
        $order = new $this->entity_name;

        return $order->where([["is_paid_at", "!=", null], ["is_resolved_at", "!=", null]])->whereRaw("CURRENT_TIMESTAMP >= DATE_ADD(is_paid_at, INTERVAL validity_in_days DAY)")->whereBetween('created_at',[$from,$until])->get();
    }

    public function getCountTotalPackageByDate($from,$until)
    {
        $order = new $this->entity_name;

        return $order->where([["is_paid_at", "!=", null], ["is_resolved_at", "!=", null]])->whereBetween('created_at',[$from,$until])->get();
    }

}
