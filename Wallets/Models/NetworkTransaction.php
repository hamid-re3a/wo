<?php

namespace Wallets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Wallets\Models\NetworkTransaction
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $transaction_hash
 * @property string $comment
 * @property string $labels
 * @property double $amount
 * @property string $block_hash
 * @property integer $block_height
 * @property integer $confirmations
 * @property integer $timestamp
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class NetworkTransaction extends Model
{
    protected $fillable = [
        'uuid',
        'transaction_hash',
        'comment',
        'labels',
        'amount',
        'block_hash',
        'block_height',
        'confirmations',
        'timestamp',
        'status',
    ];

    protected $casts = [
        'tinyInteger' => 'integer',
        'amount' => 'decimal',
        'labels' => 'json',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'wallet_network_transactions';


    /*
     * Relations
     */
    public function withdrawProfits()
    {
        return $this->hasMany(WithdrawProfit::class,'network_transaction_id','id');
    }

    /*
     * Mutators
     */
    public function setTransactionHashAttribute($value)
    {
        $this->attributes['transaction_hash'] = $value;

        $uuid = Uuid::uuid4()->toString();
        while ($this->where('uuid', $uuid)->first())
            $uuid = Uuid::uuid4()->toString();
        $this->attributes['uuid'] = $uuid;
    }
}
