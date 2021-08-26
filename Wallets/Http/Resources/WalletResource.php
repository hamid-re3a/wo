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

        $totalReceived = number_format(abs($this->transactions()->where('wallet_id',$this->id)->where('type', 'deposit')->sum('amount')) / 100,2);
        $totalSpent = number_format(abs($this->transactions()->where('wallet_id',$this->id)->where('type', 'withdraw')->sum('amount')) / 100 ,2);
        $totalTransfer = number_format(abs($this->transactions()->where('wallet_id',$this->id)->where('meta->type', 'Transfer')->sum('amount')) / 100 ,2);
        return [
            'name' => $this->name,
            'balance' => number_format($this->balanceFloat,2),
            'transactions_count' => $this->transactions->count(),
            'transfers_count' => $this->transfers->count(),
            'total_transfer' => $totalTransfer,
            'total_received' => $totalReceived,
            'total_spent' => $totalSpent
        ];
    }
}
