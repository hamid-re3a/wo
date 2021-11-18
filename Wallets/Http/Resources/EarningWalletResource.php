<?php

namespace Wallets\Http\Resources;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarningWalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        /**@var $wallet Wallet*/
        $wallet = $this->resource;

        return [
            'name' => $wallet->name,
            'balance' => (double)$wallet->balanceFloat,
            'transactions_count' => (int) $wallet->transactions()->where('wallet_id','=', $wallet->id)->count(),
            'transfers_count' => (int) $wallet->transfers()->where('from_id','=',$wallet->id)->count(),
        ];
    }
}
