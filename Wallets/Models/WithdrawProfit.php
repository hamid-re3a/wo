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
 * @property boolean $is_update_email_sent
 * @property string $payout_service
 * @property string $currency
 * @property double $pf_amount
 * @property double $crypto_amount
 * @property double $crypto_rate
 * @property double $fee
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read User $actor
 * @property-read Transaction $withdrawTransaction
 * @property-read Transaction $refundTransaction
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
        'network_transaction_id',
        'status',
        'actor_id',
        'rejection_reason',
        'is_update_email_sent',
        'payout_service',
        'currency',
        'pf_amount',
        'crypto_amount',
        'crypto_rate',
        'fee',
    ];

    protected $with = [
        'withdrawTransaction',
        'refundTransaction',
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


    /*
     * Mutators
     */

    public function setCryptoAmountAttribute($value)
    {
        $this->attributes['crypto_amount'] = number_format($value,8);
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
