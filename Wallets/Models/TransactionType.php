<?php

namespace Wallets\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    protected $table = 'wallet_transaction_types';

    protected $fillable = [
        'name',
        'description'
    ];

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class,TransactionMetaData::class);
    }

}
