<?php

namespace Wallets\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Wallets\Models\WithdrawProfit;

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
        /**@var $withdraw_request WithdrawProfit*/
        $withdraw_request = $this;

        return [
            'id' => $withdraw_request->uuid,
            'user_member_id' => $withdraw_request->user->member_id,
            'user_full_name' => $withdraw_request->user->full_name,
            'withdraw_transaction_id' => $withdraw_request->withdrawTransaction->uuid,
            'refund_transaction_id' => !empty($withdraw_request->refund_transaction_id) ? $withdraw_request->refundTransaction->uuid : null,
            'network_transaction_hash' => !empty($withdraw_request->network_transaction_id) ? $withdraw_request->networkTransaction->transaction_hash : null,
            'actor_full_name' => !empty($withdraw_request->actor_id) ? $withdraw_request->actor->full_name : null,
            'rejection_reason' => !empty($withdraw_request->rejection_reason) ? $withdraw_request->rejection_reason : null,
            'wallet_hash' => $withdraw_request->wallet_hash,
            'currency' => $withdraw_request->currency,
            'crypto_rate' => (double)$withdraw_request->crypto_rate,
            'fee' => (double)$withdraw_request->fee,
            'pf_amount' => (double)$withdraw_request->pf_amount,
            'crypto_amount' => (double)$withdraw_request->crypto_amount,
            'status' => $withdraw_request->status,
            'postponed_to' => !empty($withdraw_request->postponed_to) ? $withdraw_request->postponed_to->timestamp : null,
            'created_at' => $withdraw_request->created_at->timestamp,
            'updated_at' => $withdraw_request->updated_at->timestamp,
        ];
    }
}
