<?php

namespace Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use User\Models\User;

/**
 * Wallets/Models/WithdrawProfit
 *
 * @property int $id
 * @property string $uuid
 * @property string $wallet_hash
 * @property int $user_id
 * @property int $withdraw_transaction_id
 * @property int $refund_transaction_id
 * @property int $network_transaction_id
 * @property string $status
 * @property int $actor_id
 * @property string|null $rejection_reason
 * @property string|null $network_hash
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read User $actor
 * @property-read Transaction $withdrawTransaction
 * @property-read Transaction $refundTransaction
 * @property-read NetworkTransaction $networkTransaction
 * @mixin \Eloquent
 */
class WithdrawProfit extends Model
{
    protected $table = 'wallet_withdraw_profit_requests';

    protected $fillable = [
        'uuid',
        'wallet_hash',
        'user_id',
        'withdraw_transaction_id',
        'refund_transaction_id',
        'status',
        'actor_id',
        'rejection_reason',
        'network_hash',
    ];

    protected $with = [
        'withdrawTransaction',
        'refundTransaction',
        'network_transaction',
        'user',
        'actor'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }

    public function withdrawTransaction()
    {
        return $this->belongsTo(Transaction::class, 'withdraw_transaction_id', 'id');
    }

    public function refundTransaction()
    {
        return $this->belongsTo(Transaction::class, 'refund_transaction_id', 'id');
    }

    public function networkTransaction()
    {
        return $this->belongsTo(NetworkTransaction::class,'network_transaction_id','id');
    }

    /*
     * Mutators
     */
    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value;
    }

    public function getStatusAttribute()
    {
        switch ($this->attributes['status']) {
            case 1:
                return 'Under review';
                break;

            case 2:
                return 'Rejected';
                break;
            case 3:
                return 'Processed';
                break;

            default:
                return 'Unknown';
        }
    }
}
