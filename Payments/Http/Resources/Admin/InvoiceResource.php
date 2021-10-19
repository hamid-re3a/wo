<?php

namespace Payments\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use User\Models\User;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /**@var $user User*/
        $user = $this->user;
        $transactions = $this->transactions()->exists() ? InvoiceTransactionResource::collection($this->transactions) : null;

        return [
            'user_member_id' => $user->member_id,
            'user_full_name' => $user->full_name,
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'status' => $this->full_status,
            'amount' => $this->amount,
            'checkout_link' => $this->checkout_link,
            'is_paid' => $this->is_paid,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
            'transactions' => $transactions,
            'expiration_time' => $this->expiration_time->timestamp,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }
}
