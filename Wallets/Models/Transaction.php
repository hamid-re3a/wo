<?php

namespace Wallets\Models;

use Illuminate\Support\Facades\Log;

/**
 * Class Transaction
 * @package Wallets\Models
 * @property syncMetaData
 * @property TransactionType metaData
 */
class Transaction extends \Bavix\Wallet\Models\Transaction
{

    protected $with =[
        'metaData'
    ];

    public function metaData()
    {
        return $this->belongsToMany(TransactionType::class,'wallet_transaction_meta_data','transaction_id','type_id')
                    ->withPivot(['wallet_before_balance','wallet_after_balance']);
    }

    public function syncMetaData(array $data)
    {
        $type = TransactionType::query()->where('name',$data['type'])->first();
        if(array_key_exists('type', $data) AND $type) {
            $this->metaData()->sync([
                $type->id => [
                    'wallet_before_balance' => $data['wallet_before_balance'],
                    'wallet_after_balance' =>  $data['wallet_after_balance'],
                ]
            ]);
        } else
            Log::error('Transaction undefined type . <<<' . serialize($data) . '>>>');
    }


}
