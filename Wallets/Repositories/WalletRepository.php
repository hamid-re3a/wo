<?php

namespace Wallets\Repositories;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Support\Collection;
use User\Models\User;
use Wallets\Services\BankService;

class WalletRepository
{
    /**@var $transaction_repository TransactionRepository */
    private $transaction_repository;

    public function __construct()
    {
        $this->transaction_repository = new TransactionRepository();
    }

    public function getOverAllSum(int $wallet_id = null, int $user_id = null): array
    {
        $main_types_sum = $this->transaction_repository->getTransactionsSumByMainTypes($user_id,$wallet_id,['deposit','withdraw']);
        $pivot_types_sum = $this->transaction_repository->getTransactionsSumByPivotTypes($user_id,$wallet_id,['Funds transferred']);

        return [
            (double) $main_types_sum['deposit_sum'],
            (double) $main_types_sum['withdraw_sum'],
            (double) $pivot_types_sum['funds_transferred_sum']
        ];
    }

    public function getWalletOverallBalanceChart($type, int $wallet_id = null,$wallet_name = null)
    {
        $that = $this;
        $function_transaction_collection = function ($from_day, $to_day) use ($that, $wallet_id, $wallet_name) {
            return $that->transaction_repository->getTransactionsByDateAndTypeCollection('created_at', $from_day, $to_day, $wallet_id, $type = null, $wallet_name);
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

    public function getWalletTransactionsByTypeChart($type, int $wallet_id = null, $wallet_name = null)
    {
        $that = $this;
        $function_transaction_transfer_collection = function ($from_day, $to_day) use ($that, $wallet_id) {
            return $that->transaction_repository->getTransactionsByDateAndTypeCollection('created_at', $from_day, $to_day, $wallet_id, 'Funds transferred');
        };

        $function_transaction_giftcode_collection = function ($from_day, $to_day) use ($that, $wallet_id) {
            return $that->transaction_repository->getTransactionsByDateAndTypeCollection('created_at', $from_day, $to_day, $wallet_id, 'Gift code created');
        };

        $function_transaction_purchase_collection = function ($from_day, $to_day) use ($that, $wallet_id) {
            return $that->transaction_repository->getTransactionsByDateAndTypeCollection('created_at', $from_day, $to_day, $wallet_id, 'Package purchased');
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
                return $that->transaction_repository->getTransactionsByDateAndTypeCollection('created_at', $from_day, $to_day, $wallet_id, $commission_name);
            };
            $result[$commission_name] = chartMaker($type, $function_transactions_collection, $sub_function);
        }
        return $result;
    }

    public function transferFunds(Wallet $from_wallet,Wallet $to_wallet,$amount, $description = null) : Transfer
    {
        /**@var $user User*/
        $user = $from_wallet->holder;
        $bank_service = new BankService($user);
        return $bank_service->transfer(
            $from_wallet,
            $to_wallet,
            $amount,
            $description
        );

    }
}
