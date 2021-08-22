<?php

namespace Giftcode\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class GiftcodeResource extends JsonResource
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
            'id' => $this->uuid,
            'package_name' => $this->package_name,
            'code' => $this->code,
            'expiration_date' => $this->expiration_date,
            'packages_cost_in_usd' => $this->packages_cost_in_usd,
            'registration_fee_in_usd' => $this->registration_fee_in_usd,
            'total_cost_in_usd' => $this->total_cost_in_usd,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
