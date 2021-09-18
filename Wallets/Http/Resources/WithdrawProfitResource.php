<?php

namespace Wallets\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class WithdrawProfitResource extends JsonResource
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
            'user_full_name' => $this->user->full_name,
            'withdraw_transaction_id' => $this->withdrawTransaction->uuid,
            'refund_transaction_id' => !empty($this->refund_transaction_id) ? $this->refundTransaction->uuid : null,
            'actor_full_name' => !empty($this->actor_id) ? $this->actor->full_name : null,
            'rejection_reason' => !empty($this->rejection_reason) ? $this->rejection_reason : null,

            'status' => $this->status,
            'network_hash' => $this->network_hash
        ];
    }
}
