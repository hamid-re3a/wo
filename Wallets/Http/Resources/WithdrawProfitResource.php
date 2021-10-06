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
            'user_member_id' => $this->user->member_id,
            'user_full_name' => $this->user->full_name,
            'withdraw_transaction_id' => $this->withdrawTransaction->uuid,
            'refund_transaction_id' => !empty($this->refund_transaction_id) ? $this->refundTransaction->uuid : null,
            'actor_full_name' => !empty($this->actor_id) ? $this->actor->full_name : null,
            'rejection_reason' => !empty($this->rejection_reason) ? $this->rejection_reason : null,
            'wallet_hash' => $this->wallet_hash,
            'currency' => $this->currency,
            'crypto_rate' => $this->crypto_rate,
            'fee' => $this->fee,
            'pf_amount' => $this->pf_amount,
            'crypto_amount' => $this->crypto_amount,
            'status' => $this->status,
            'postponed_to' => !empty($this->postponed_to) ? $this->postponed_to->timestamp : null,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }
}
