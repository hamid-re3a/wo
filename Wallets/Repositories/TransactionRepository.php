<?php


namespace Wallets\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Wallets\Models\Transaction;

class TransactionRepository
{
    private $entity = Transaction::class;

    public function getTransactionByDateCollection($date_field,$from_date,$to_date,$wallet_id = null,$type = null)
    {
        try {

            /**@var $transaction Transaction*/
            $transaction = new $this->entity;
            $transaction = $transaction->query();

            if(!empty($wallet_id))
                $transaction->where('wallet_id','=',$wallet_id);
            else
                $transaction->where('wallet_id','<>', 1);

            if($type)
                $transaction->whereHas('metaData', function (Builder $query) use($type) {
                    $query->where('wallet_transaction_types.name', '=', trim($type));
                });

            $from_date = Carbon::parse($from_date)->toDateTimeString();
            $to_date = Carbon::parse($to_date)->toDateTimeString();

            return $transaction->whereBetween($date_field,[$from_date,$to_date])->get();
        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\TransactionRepository@getTransactionByDateCollection => ' . $exception->getMessage());
            throw new \Exception(trans('wallets.responses.something-went-wrong'),500);
        }
    }
}
