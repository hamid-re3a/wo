<?php

namespace Payments\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'status' => $this->full_status,
            'rate' => $this->rate,
            'amount' => $this->amount,
            'checkout_link' => $this->checkout_link,
            'is_paid' => $this->is_paid,
            'paid_amount' => $this->paid_amount,
            'paid_amount_pf' => usdToPf($this->rate * $this->paid_amount),
            'due_amount' => $this->due_amount,
            'due_amount_pf' => usdToPf($this->rate * $this->due_amount),
            'expiration_time' => $this->expiration_time->timestamp,
            'transactions' => $transactions,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }
}
