<?php

namespace Payments\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $rate = $this->invoice->rate;
        return [
            'hash' => $this->hash,
            'received_date' => $this->received_date->timestamp,
            'value' => (double)$this->value,
            'value_pf' => (double)usdToPf($rate * $this->value),
            'fee' => (double)$this->fee,
            'fee_pf' => (double)usdToPf($rate * $this->fee),
            'status' => $this->status,
            'destination' => $this->destination,
        ];
    }
}
