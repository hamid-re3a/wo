<?php

namespace Wallets\Http\Resources;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use User\Models\User;
use Wallets\Repositories\WalletRepository;

class DepositWalletResource extends JsonResource
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
        /**@var $user User*/
        $user = $wallet->holder;
        $wallet_repository = new WalletRepository($user);
        $total_sum = $wallet_repository->getOverAllSum($wallet);

        return [
            'name' => $wallet->name,
            'balance' => (double)$wallet->balanceFloat,
            'transactions_count' => (int) $wallet->transactions()->count(),
            'transfers_count' => (int) $wallet->transfers()->count(),
            'total_transfer' => $total_sum->total_transfer ?$total_sum->total_transfer / 100 : 0,
            'total_received' => $total_sum->total_received ? $total_sum->total_received / 100 : 0,
            'total_spent' => $total_sum->total_spent ? $total_sum->total_spent / 100 : 0
        ];
    }
}
