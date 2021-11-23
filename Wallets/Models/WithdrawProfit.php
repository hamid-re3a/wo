<?php

namespace Wallets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Payments\Models\BPSNetworkTransactions;
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
 * @property string|null $act_reason
 * @property boolean $is_update_email_sent
 * @property string $payout_service
 * @property string $currency
 * @property double $pf_amount
 * @property double $crypto_amount
 * @property double $crypto_rate
 * @property double $fee
 * @property double $total
 * @property Carbon $postponed_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read User $actor
 * @property-read Transaction $withdrawTransaction
 * @property-read Transaction $refundTransaction
 * @property-read BPSNetworkTransactions $networkTransaction
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
        'act_reason',
        'is_update_email_sent',
        'payout_service',
        'currency',
        'pf_amount',
        'crypto_amount',
        'crypto_rate',
        'fee',
        'postponed_to'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'postponed_to' => 'datetime'
    ];

    protected $with = [
        'withdrawTransaction',
        'refundTransaction',
        'networkTransaction',
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
        return $this->belongsTo(BPSNetworkTransactions::class,'network_transaction_id','id');
    }

    /*
     * Methods
     * Scopes
     */
    public function getTotalAmount()
    {
        return (double)($this->attributes['pf_amount'] + $this->attributes['fee']);
    }

    public function scopeFilter($query)
    {
        if(request()->has('withdraw_transaction_id')) {
            $id = request()->get('withdraw_transaction_id');
            $query->where('withdraw_transaction_id','LIKE', "%{$id}%");
        }

        if(request()->has('refund_transaction_id')) {
            $id = request()->get('refund_transaction_id');
            $query->where('refund_transaction_id','LIKE', "%{$id}%");
        }

        if(request()->has('member_id'))
            $query->whereHas('user', function($subQuery){
                $id = request()->get('member_id');
                $subQuery->where('member_id','LIKE',"%{$id}%");
            });
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
                return WALLET_WITHDRAW_REQUEST_STATUS_UNDER_REVIEW_STRING;
                break;

            case 2:
                return WALLET_WITHDRAW_REQUEST_STATUS_REJECT_STRING;
                break;
            case 3:
                return WALLET_WITHDRAW_REQUEST_STATUS_PROCESS_STRING;
                break;
            case 4:
                return WALLET_WITHDRAW_REQUEST_STATUS_POSTPONE_STRING;
                break;
            case 5:
                return WALLET_WITHDRAW_REQUEST_STATUS_REVERT_STRING;
                break;
            default:
                return 'Unknown';
        }
    }

}
