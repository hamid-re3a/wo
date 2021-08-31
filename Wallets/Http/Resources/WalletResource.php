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

        $totalReceived = walletPfAmount(abs($this->transactions()->where('wallet_id',$this->id)->where('type', 'deposit')->sum('amount')) / 100);
        $totalSpent = walletPfAmount(abs($this->transactions()->where('wallet_id',$this->id)->where('type', 'withdraw')->sum('amount')) / 100 );
        $totalTransfer = walletPfAmount(abs($this->transactions()->where('wallet_id',$this->id)->where('meta->type', 'Transfer')->sum('amount')) / 100 );
        return [
            'name' => $this->name,
            'balance' => walletPfAmount($this->balanceFloat),
            'transactions_count' => (int) $this->transactions->count(),
            'transfers_count' => (int) $this->transfers->count(),
            'total_transfer' => (int) $totalTransfer,
            'total_received' => (int) $totalReceived,
            'total_spent' => (int) $totalSpent
        ];
    }
}
