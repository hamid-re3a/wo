<?php

namespace Wallets\Models;

use Illuminate\Support\Facades\Log;

/**
 * Class Transaction
 * @package Wallets\Models
 * @property TransactionType metaData
 */
class Transaction extends \Bavix\Wallet\Models\Transaction
{

    protected $with = [
        'metaData'
    ];

    public function metaData()
    {
        return $this->belongsToMany(TransactionType::class, 'wallet_transaction_meta_data', 'transaction_id', 'type_id')
            ->withPivot(['wallet_before_balance', 'wallet_after_balance']);
    }

    public function syncMetaData(array $data)
    {
        try {
            if (array_key_exists('type', $data)) {
                $sub_type = null;
                $type = TransactionType::query()->firstOrCreate([
                    'name' => $data['type'],
                    'description' => null
                ]);
                if (array_key_exists('sub_type',$data) AND !empty($data['sub_type'])) {
                    $sub_type = $type->subTypes()->firstOrCreate([
                        'name' => $data['sub_type'],
                        'parent_id' => $type->id,
                        'description' => null
                    ]);
                }

                $typeId = $sub_type ? $sub_type->id : $type->id;
                $this->metaData()->sync([
                    $typeId => [
                        'wallet_before_balance' => $data['wallet_before_balance'],
                        'wallet_after_balance' => $data['wallet_after_balance'],
                    ]
                ]);
            } else {
                Log::error('Transaction undefined type . <<<' . serialize($data) . '>>>');
                throw new \Exception(trans('wallet.responses.something-went-wrong'));
            }
        } catch (\Throwable $exception) {
            throw new \Exception(trans('wallet.responses.something-went-wrong'), $exception->getCode());
        }
    }


}
