<?php

namespace Wallets\Repositories;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use User\Models\User;
use Wallets\Models\Transaction;

class WalletRepository
{
    /**@var $transaction_repository TransactionRepository */
    private $transaction_repository;

    public function __construct()
    {
        $this->transaction_repository = new TransactionRepository();
    }

    public function getOverAllSum(Wallet $wallet)
    {
        return User::query()->where('id', $wallet->holder_id)->withSumQuery([
            'transactions.amount AS total_received' => function (Builder $query) use ($wallet) {
                $query->where('wallet_id', '=', $wallet->id);
                $query->where('type', '=', 'deposit');
            }
        ])
            ->withSumQuery([
                'transactions.amount AS total_spent' => function (Builder $query) use ($wallet) {
                    $query->where('wallet_id', '=', $wallet->id);
                    $query->where('type', 'withdraw');

                }
            ])
            ->withSumQuery([
                'transactions.amount AS total_transfer' => function (Builder $query) use ($wallet) {
                    $query->where('wallet_id', '=', $wallet->id);
                    $query->where('type', 'withdraw');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'Funds transferred');
                    });
                }
            ])
            ->first();
    }

    public function getTransactionByTypesSum(int $user_id = null, array $types = null)
    {
        $counts_query = User::query();
        $keys = [];
        if (!empty($user_id))
            $counts_query->whereId($user_id);

        foreach ($types AS $type) {
            $key = Str::replace(' ', '_', Str::lower($type)) . '_sum';
            $keys[] = $key;

            $counts_query->withSumQuery(['transactions.amount AS ' . $key => function (Builder $query) use ($type) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) use ($type) {
                        $subQuery->where('name', '=', $type);
                    });
                }]
            );

        }

        $counts_query = $counts_query->first()->toArray();

        $results = [];

        foreach($keys AS $key)
            $results[$key] = !empty($counts_query[$key]) ? $counts_query[$key] / 100 : (double)0.00;

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
