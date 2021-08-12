<?php

namespace Wallets\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
//            'id' => $this->id,
            'name' => $this->name,
            'balance' => number_format($this->balanceFloat,2),
            'transactions_count' => $this->transactions->count(),
            'transfers' => $this->transfers->count()
        ];
    }
}
