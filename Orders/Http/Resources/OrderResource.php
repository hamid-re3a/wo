<?php

namespace Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,
            'total_cost_in_pf' => $this->total_cost_in_pf,
            'package_cost_in_usd' => $this->packages_cost_in_pf,
            'registration_fee_in_pf' => $this->registration_fee_in_pf,
            'is_paid_at' => !empty($this->is_paid_at) ? $this->is_paid_at->timestamp : null,
            'payment_currency' => $this->payment_currency,
            'payment_type' => $this->payment_type,
            'expires_at' => !empty($this->expires_at) ? $this->expires_at->timestamp : null,
            'created_at' => !empty($this->created_at) ? $this->created_at->timestamp : null,
        ];
    }
}
