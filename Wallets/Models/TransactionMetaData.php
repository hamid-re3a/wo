<?php

namespace Wallets\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionMetaData extends Model
{
    protected $table = 'wallet_transaction_meta_data';

    protected $fillable = [
        'transaction_id',
        'type_id',
        'description',
        'wallet_before_balance',
        'wallet_after_balance'
    ];

    public $timestamps = false;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

}
