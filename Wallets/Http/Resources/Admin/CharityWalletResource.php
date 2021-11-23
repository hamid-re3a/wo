<?php

namespace Wallets\Http\Resources\Admin;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Wallets\Repositories\WalletRepository;

class CharityWalletResource extends JsonResource
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
        $wallet->refreshBalance();
        $wallet_repository = new WalletRepository();
        list($total_received,$total_spent,$total_transfer) = $wallet_repository->getOverAllSum($wallet->id,$wallet->holder_id);

        return [
            'name' => $wallet->name,
            'balance' => (double)$wallet->balanceFloat,
            'transactions_received_count' => (int) $wallet->transactions()->where('wallet_id','=', $wallet->id)->where('type','=','deposit')->count(),
            'transactions_spent_count' => (int) $wallet->transactions()->where('wallet_id','=', $wallet->id)->where('type','=','withdraw')->count(),
            'total_received' => (double)$total_received,
            'total_spent' => (double)$total_spent
        ];
    }
}
