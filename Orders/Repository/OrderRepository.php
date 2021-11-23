<?php

namespace Orders\Repository;

use Orders\Models\Order;

class OrderRepository
{
    protected $entity_name = Order::class;

    public function getResolvedOrdersCount()
    {
        /**@var $order Order */
        $order = new $this->entity_name;
        return $order->query()->resolved()->count();
    }

    public function getCanceledOrdersCount()
    {
        /**@var $order Order */
        $order = new $this->entity_name;
        return $order->query()->canceled()->count();
    }

    public function getActiveOrdersSum()
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->resolved()->active()->sum('total_cost_in_pf');
    }

    public function getPaidOrdersSum()
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->resolved()->sum('total_cost_in_pf');
    }

    public function getActiveOrdersCount()
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->resolved()->active()->count();
    }

    public function getExpiredOrdersCount()
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->resolved()->expired()->count();
    }


    public function getActivePackageByDateCollection($from, $until)
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->select('created_at')->resolved()->active()->whereBetween('created_at', [$from, $until])->get();
    }

    public function getActiveOrderWithPackageByDateCollection($from, $until,$id = null)
    {
        /**@var $order Order */
        $order = new $this->entity_name;


        if(is_null($id))
            return $order->select(['created_at','package_id'])->resolved()->active()->whereBetween('created_at', [$from, $until])->get();
        else
            return $order->select(['created_at','package_id'])->resolved()->active()->whereBetween('created_at', [$from, $until])->where('user_id',$id)->get();
    }

    public function getActiveOrderWithPackageByDateForUserCollection($from, $until, $user)
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->resolved()->active()->whereBetween('created_at', [$from, $until])->where('user_id', $user->id)->get();
    }

    public function getExpiredPackageByDateCollection($from, $until)
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->select('created_at')->resolved()->expired()->whereBetween('created_at', [$from, $until])->get();
    }

    public function getExpiredPackageByDateForUserCollection($from, $until, $user)
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->select('created_at')->resolved()->expired()->whereBetween('created_at', [$from, $until])->where('user_id', $user->id)->get();
    }

    public function getTotalPackageByDateCollection($from, $until)
    {
        /**@var $order Order */
        $order = new $this->entity_name;

        return $order->select('created_at')->resolved()->whereBetween('created_at', [$from, $until])->get();
    }

}
