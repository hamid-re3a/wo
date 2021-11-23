<?php

namespace Payments\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Payments\Http\Resources\InvoiceTransactionResource;

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
        $transactions = $this->transactions ? InvoiceTransactionResource::collection($this->transactions) : null;
        return [
            'user_member_id' => $this->user->member_id,
            'user_full_name' => $this->user->full_name,
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'status' => $this->full_status,
            'rate' => (double)$this->rate,
            'amount' => (double)$this->amount,
            'amount_pf' => (double)$this->pf_amount,
            'checkout_link' => $this->checkout_link,
            'is_paid' => $this->is_paid,
            'paid_amount' => (double)$this->paid_amount,
            'paid_amount_pf' => (double)usdToPf($this->paid_amount * $this->rate),
            'due_amount' => (double)$this->due_amount,
            'due_amount_pf' => (double)usdToPf($this->due_amount * $this->rate),
            'is_refund_at' => $this->is_refund_at ? $this->is_refund_at->timestamp : null,
            'refunder_full_name' => $this->refunder_user_id ? $this->refunder->full_name : null,
            'transactions' => $transactions,
            'expiration_time' => $this->expiration_time ? $this->expiration_time->timestamp : null,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }
}
