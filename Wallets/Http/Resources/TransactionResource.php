<?php

namespace Wallets\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
        $type = $this->type;
        $description = null;

        if(!empty($this->meta)){
            if(array_key_exists('type', $this->meta))
                $type = $this->meta['type'];
            if(array_key_exists('description', $this->meta))
                $description = $this->meta['description'];
        }

        return [
            'id' => $this->uuid,
            'wallet' => $this->wallet->name,
            'type' => Str::ucfirst($type),
            'amount' => (float) walletPfAmount($this->amountFloat),
            'new_balance' => (float) walletPfAmount($this->new_balance / 100),
            'description' => $description,
            'confirmed' => $this->confirmed,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
