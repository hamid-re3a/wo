<?php

namespace Wallets\Repositories;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use User\Models\User;
use Wallets\Models\Transaction;

class WalletRepository
{

    private $transaction_entity;
    private $user;

    public function __construct(User $user)
    {
        $this->transaction_entity = Transaction::class;
        $this->user = $user;
    }

    public function getOverAllSum(Wallet $wallet)
    {
        return User::query()->where('id',$this->user->id)->withSumQuery([
            'transactions.amount AS total_received' => function (Builder $query) use($wallet) {
                $query->where('wallet_id','=',$wallet->id);
                $query->where('type', '=', 'deposit');
            }
        ])
            ->withSumQuery([
                'transactions.amount AS total_spent' => function (Builder $query) use($wallet) {
                    $query->where('wallet_id','=',$wallet->id);
                    $query->where('type', 'withdraw');

                }
            ])
            ->withSumQuery([
                'transactions.amount AS total_transfer' => function (Builder $query) use($wallet) {
                    $query->where('wallet_id','=',$wallet->id);
                    $query->where('type','withdraw');
                    $query->whereHas('metaData', function(Builder $subQuery) {
                        $subQuery->where('name','=','Funds transferred');
                    });
                }
            ])
            ->first();
    }
}
