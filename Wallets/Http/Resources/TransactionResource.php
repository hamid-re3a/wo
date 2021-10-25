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

        $metaData = $this->metaData->first();

        return [
            'id' => $this->uuid,
            'wallet' => $this->wallet->name,
            'type' => $metaData ? $metaData->name : null,
            'amount' => $this->amountFloat,
            'before_balance' => $metaData ? $metaData->pivot->wallet_before_balance : 0,
            'after_balance' => $metaData ? $metaData->pivot->wallet_after_balance : 0,
            'description' => $this->meta AND array_key_exists('description',$this->meta) ? $this->meta['description'] : null,
//            'confirmed' => $this->confirmed,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
