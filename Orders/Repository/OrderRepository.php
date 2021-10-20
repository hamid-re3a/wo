<?php

namespace Orders\Repository;

use Orders\Models\Order;

class OrderRepository
{
    protected $entity_name = Order::class;

    public function getCountSubscriptions()
    {
        $order = new $this->entity_name;
        return $order_package_is_paid = $order->resolved()->count();
    }


    public function getCountActivePackage()
    {
        $order = new $this->entity_name;

        return $order->resolved()->active()->count();
    }

    public function getCountExpiredPackage()
    {
        $order = new $this->entity_name;

        return $order->resolved()->expired()->count();
    }


    public function getActivePackageByDateCollection($from, $until)
    {
        $order = new $this->entity_name;

        return $order->resolved()->active()->whereBetween('created_at', [$from, $until])->get();
    }

    public function getActiveOrderWithPackageByDateCollection($from, $until)
    {
        $order = new $this->entity_name;

        return $order->resolved()->active()->whereBetween('created_at', [$from, $until])->get();
    }

    public function getActiveOrderWithPackageByDateForUserCollection($from, $until,$user)
    {
        $order = new $this->entity_name;

        return $order->resolved()->active()->whereBetween('created_at', [$from, $until])->where('user_id',$user->id)->get();
    }

    public function getExpiredPackageByDateCollection($from, $until)
    {
        $order = new $this->entity_name;

        return $order->resolved()->expired()->whereBetween('created_at', [$from, $until])->get();
    }
    public function getExpiredPackageByDateForUserCollection($from, $until,$user)
    {
        $order = new $this->entity_name;

        return $order->resolved()->expired()->whereBetween('created_at', [$from, $until])->where('user_id',$user->id)->get();
    }

    public function getTotalPackageByDateCollection($from, $until)
    {
        $order = new $this->entity_name;

        return $order->resolved()->whereBetween('created_at', [$from, $until])->get();
    }

}
