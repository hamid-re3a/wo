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
            'user_member_id' => $this->user->member_id,
            'user_full_name' => $this->user->full_name,
            'package_name' => $this->package_name,
            'code' => $this->code,
            'expiration_date' => $this->expiration_date,
            'packages_cost_in_pf' => $this->packages_cost_in_pf,
            'registration_fee_in_pf' => $this->registration_fee_in_pf,
            'total_cost_in_pf' => $this->total_cost_in_pf,
            'redeem_date' => $this->redeem_date ? $this->redeem_date->timestamp : null,
            'redeem_user_full_name' => $this->redeemer_full_name,
            'created_at' => $this->created_at->timestamp,
            'status' => $this->status,
        ];
    }
}
