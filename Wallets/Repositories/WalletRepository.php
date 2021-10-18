<?php

namespace Wallets\Repositories;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use User\Models\User;

class WalletRepository
{

    private $transaction_repository;

    public function __construct(TransactionRepository $transaction_repository)
    {
        $this->transaction_repository =  $transaction_repository;
    }

    public function getOverAllSum(Wallet $wallet)
    {
        return User::query()->where('id',$wallet->holder_id)->withSumQuery([
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

    public function getUserDepositWalletOverviewBalance($type,Wallet $wallet)
    {
        $that = $this;
        $function_transaction_collection = function ($from_day, $to_day) use ($that,$wallet) {
            return $that->transaction_repository->getTransactionByDateCollection('created_at',$from_day, $to_day,$wallet->id);
        };

        $sub_function = function ($collection, $intervals) {
            /**@var $collection Collection*/
            return $collection->whereBetween('created_at', $intervals)->sum(function ($transaction){
                return $transaction->metaData->first()->pivot->wallet_after_balance / 100;
            });
        };

        $result = [];
        $result['balance'] = chartMaker($type, $function_transaction_collection, $sub_function);
        return $result;
    }
}
