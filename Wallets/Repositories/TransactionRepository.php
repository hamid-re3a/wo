<?php


namespace Wallets\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
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
            Log::error('Wallets\Repositories\TransactionRepository@getTransactionByDateCollection => ' . $exception->getMessage());
            throw new \Exception(trans('wallets.responses.something-went-wrong'),500);
        }
    }
}
