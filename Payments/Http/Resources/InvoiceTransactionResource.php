<?php

namespace Payments\Http\Resources;

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
        return [
            'hash' => $this->hash,
            'received_date' => $this->received_date->timestamp,
            'value' => $this->value,
            'fee' => $this->fee,
            'status' => $this->status,
            'destination' => $this->destination,
        ];
    }
}
