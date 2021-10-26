<?php

namespace Wallets\Repositories;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use User\Models\User;
use Wallets\Models\Transaction;
use Wallets\Models\TransactionType;

class WalletRepository
{
    /**@var $transaction_repository TransactionRepository */
    private $transaction_repository;

    public function __construct()
    {
        $this->transaction_repository = new TransactionRepository();
    }

    public function getOverAllSum(Wallet $wallet = null)
    {
        $sum_query = User::query();

        if ($wallet)
            $sum_query->where('id', $wallet->holder_id);

        return $sum_query->withSumQuery([
            'transactions.amount AS total_received' => function (Builder $query) use ($wallet) {
                if ($wallet)
                    $query->where('wallet_id', '=', $wallet->id);
                $query->where('type', '=', 'deposit');
            }
        ])
            ->withSumQuery([
                'transactions.amount AS total_spent' => function (Builder $query) use ($wallet) {
                    if ($wallet)
                        $query->where('wallet_id', '=', $wallet->id);
                    $query->where('type', 'withdraw');

                }
            ])
            ->withSumQuery([
                'transactions.amount AS total_transfer' => function (Builder $query) use ($wallet) {
                    if ($wallet)
                        $query->where('wallet_id', '=', $wallet->id);
                    $query->where('type', 'withdraw');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'Funds transferred');
                    });
                }
            ])
            ->first();
    }

    public function getTransactionSumByTypes(int $user_id = null, array $types = null)
    {
        $results = [];

        $types_collection = TransactionType::query()->whereIn('name', $types)->select(['id', 'name'])->get();

        foreach ($types_collection AS $type) {
            $key = Str::replace(' ', '_', Str::lower($type->name)) . '_sum';
            $sum_query = null;
            $sum_query = Transaction::query();

            if (!empty($user_id))
                $sum_query->where('payable_id', '=', $user_id);

            $results[$key] =
                $sum_query->whereHas('metaData', function (Builder $subQuery) use ($type) {
                    $subQuery->where('wallet_transaction_meta_data.type_id', '=', $type->id);
                })->sum('amount');

        }

        return $results;
    }

    public function getWalletOverallBalance($type, int $wallet_id = null)
    {
        $that = $this;
        $function_transaction_collection = function ($from_day, $to_day) use ($that, $wallet_id) {
            return $that->transaction_repository->getTransactionByDateCollection('created_at', $from_day, $to_day, $wallet_id);
        };

        $sub_function = function ($collection, $intervals) {
            /**@var $collection Collection */
            return $collection->whereBetween('created_at', $intervals)->sum(function ($transaction) {
                return $transaction->amount / 100;
            });
        };

        $result = [];
        $result['balance'] = chartMaker($type, $function_transaction_collection, $sub_function);
        return $result;
    }

    public function getWalletInvestmentChart($type, int $wallet_id = null)
    {
        $that = $this;
        $function_transaction_transfer_collection = function ($from_day, $to_day) use ($that, $wallet_id) {
            return $that->transaction_repository->getTransactionByDateCollection('created_at', $from_day, $to_day, $wallet_id, 'Funds transferred');
        };

        $function_transaction_giftcode_collection = function ($from_day, $to_day) use ($that, $wallet_id) {
            return $that->transaction_repository->getTransactionByDateCollection('created_at', $from_day, $to_day, $wallet_id, 'Gift code created');
        };

        $function_transaction_purchase_collection = function ($from_day, $to_day) use ($that, $wallet_id) {
            return $that->transaction_repository->getTransactionByDateCollection('created_at', $from_day, $to_day, $wallet_id, 'Package purchased');
        };

        $sub_function = function ($collection, $intervals) {
            /**@var $collection Collection */
            return $collection->whereBetween('created_at', $intervals)->sum(function ($transaction) {
                return abs($transaction->amount / 100);
            });
        };

        $result = [];
        $result['transfer'] = chartMaker($type, $function_transaction_transfer_collection, $sub_function);
        $result['giftcode'] = chartMaker($type, $function_transaction_giftcode_collection, $sub_function);
        $result['purchase'] = chartMaker($type, $function_transaction_purchase_collection, $sub_function);
        return $result;
    }

    public function getCommissionsChart($type, array $commissions, int $wallet_id = null)
    {
        $that = $this;

        $sub_function = function ($collection, $intervals) {
            /**@var $collection Collection */
            return $collection->whereBetween('created_at', $intervals)->sum(function ($transaction) {
                return abs($transaction->amount / 100);
            });
        };

        $result = [];

        foreach ($commissions AS $commission_name) {
            $function_transactions_collection = function ($from_day, $to_day) use ($that, $wallet_id, $commission_name) {
                return $that->transaction_repository->getTransactionByDateCollection('created_at', $from_day, $to_day, $wallet_id, $commission_name);
            };
            $result[$commission_name] = chartMaker($type, $function_transactions_collection, $sub_function);
        }
        return $result;
    }
}
