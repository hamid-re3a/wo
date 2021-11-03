<?php


namespace Wallets\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Wallets\Models\Transaction;

class TransactionRepository
{
    private $entity = Transaction::class;

    public function getTransactionsByDateAndTypeCollection($date_field, $from_date, $to_date, $wallet_id = null, $type = null, $wallet_name = null)
    {
        try {

            /**@var $transaction Transaction*/
            $transaction = new $this->entity;
            $transactions = $transaction->query();

            if(!empty($wallet_id))
                $transactions->where('wallet_id','=',$wallet_id);
            else
                $transactions->where('transactions.wallet_id','<>', 1);

            if(!empty($wallet_name))
                $transactions->whereHas('wallet', function(Builder $query) use($wallet_name){
                    $query->where('name','=',$wallet_name);
                });

            if($type)
                $transactions->whereHas('metaData', function (Builder $query) use($type) {
                    $query->where('wallet_transaction_types.name', '=', $type);
                });

            $from_date = Carbon::parse($from_date)->startOfDay()->toDateTimeString();
            $to_date = Carbon::parse($to_date)->endOfDay()->toDateTimeString();

            return $transactions->whereBetween($date_field,[$from_date,$to_date])->get();
        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\TransactionRepository@getTransactionsByDateAndTypeCollection => ' . $exception->getMessage());
            throw new \Exception(trans('wallets.responses.something-went-wrong'),500);
        }
    }

    public function getTransactionsSumByPivotTypes(int $user_id = null,int $wallet_id = null, array $types = null)
    {
        $results = [];

        foreach ($types AS $type) {
            $key = str_replace(' ', '_', Str::lower($type)) . '_sum';
            $sum_query = null;
            /**@var $transaction Transaction*/
            $transaction = new $this->entity;
            $sum_query = $transaction->query();

            if (!empty($user_id))
                $sum_query->where('payable_id', '=', $user_id);

            if (!empty($wallet_id))
                $sum_query->where('wallet_id', '=', $wallet_id);

            $results[$key] =
                (float) $sum_query->whereHas('metaData', function (Builder $subQuery) use ($type) {
                    $subQuery->where('wallet_transaction_types.name', '=', $type);
                })->sum('amount') / 100;

        }

        return $results;
    }

    public function getTransactionsSumByMainTypes(int $user_id = null,int $wallet_id = null, array $types = null)
    {
        $results = [];

        foreach ($types AS $type) {
            $key = str_replace(' ', '_', Str::lower($type)) . '_sum';
            $sum_query = null;
            /**@var $transaction Transaction*/
            $transaction = new $this->entity;
            $sum_query = $transaction->query();

            if (!empty($user_id))
                $sum_query->where('payable_id', '=', $user_id);

            if(!empty($wallet_id))
                $sum_query->where('wallet_id','=',$wallet_id);

            $results[$key] =
                (float) $sum_query->where('type','=', $type)->sum('amount') / 100;

        }

        return $results;
    }
}
