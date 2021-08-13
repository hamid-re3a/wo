<?php

namespace Wallets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'balance' => number_format($this->balanceFloat,2),
            'transactions_count' => $this->transactions->count(),
            'transfers_count' => $this->transfers->count()
        ];
    }
}
