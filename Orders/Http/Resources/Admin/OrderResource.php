<?php

namespace Orders\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Orders\Models\Order;
use User\Models\User;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * @var $user User
         * @var $order Order
         */
        $order = $this;
        $user = $order->user;

        return [
            'id'                        => $order->id,
            'user_member_id'            => $user->member_id ,
            'user_full_name'            => $user->full_name ,
            'package_name'              => $order->package_name,
            'total_cost_in_pf'          => $order->total_cost_in_pf,
            'package_cost_in_pf'        => $order->packages_cost_in_pf,
            'registration_fee_in_pf'    => $order->registration_fee_in_pf,
            'is_paid_at'                => !empty($order->is_paid_at) ? $order->is_paid_at->timestamp : null,
            'payment_currency'          => $order->payment_currency,
            'payment_type'              => $order->payment_type,
            'expires_at'                => !empty($order->expires_at) ? $order->expires_at->timestamp : null,
            'created_at'                => !empty($order->created_at) ? $order->created_at->timestamp : null,
        ];
    }
}
