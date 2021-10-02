<?php

namespace Payments\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\NetworkTransaction
 *
 * BTCPayServer network transactions
 *
 * @property int $id
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
class BPSNetworkTransactions extends Model
{
    protected $fillable = [
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

    protected $table = 'payment_payout_network_transactions';

}
