<?php

namespace Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Orders\Models\Order;

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
        /**@var $order Order*/
        $order = $this;

        return [
            'id'                        => $order->id,
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
