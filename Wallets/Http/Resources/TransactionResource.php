<?php

namespace Wallets\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'wallet' => $this->wallet->name,
            'type' => $this->type,
            'amount' => number_format($this->amountFloat,2),
            'description' => $this->meta ? $this->meta['description'] : null,
            'confirmed' => $this->confirmed,
            'created_at' => $this->created_at,
        ];
    }
}
