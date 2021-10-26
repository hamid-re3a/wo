<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use User\Models\User;

/**
 * Payments\Models\Invoice
 *
 * @property int $id
 * @property int $payable_id
 * @property string $payable_type
 * @property int $amount
 * @property string|null $transaction_id
 * @property string|null $checkout_link
 * @property string|null $status
 * @property string $additional_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $full_status
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice orders()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice depositWallets()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice paid()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice notPaid()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice paidOver()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice paidOverNotRefunded()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice refunded()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice expired()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice notExpired()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereAdditionalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCheckoutLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property float $paid_amount
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaidAmount($value)
 * @property int $is_paid
 * @property float $due_amount
 * @property float $rate
 * @property float $deposit_amount
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDueAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereIsPaid($value)
 * @property string|null $expiration_time
 * @property string|null $is_refund_at
 * @property int|null $refunder_user_id
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereExpirationTime($value)
 * @property float $pf_amount
 * @property string $payment_type
 * @property string $payment_currency
 * @property string|null $payment_driver
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePfAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDepositAmount($value)
 * @property int|null $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\Payments\Models\InvoiceTransaction[] $transactions
 * @property-read \User\Models\User[] $user
 * @property-read \User\Models\User[] $refunder
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePayableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePayableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereUserId($value)
 */
class Invoice extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expiration_time' => 'datetime',
        'is_refund_at' => 'datetime',
        'is_paid' => 'boolean'
    ];

    public function getFullStatusAttribute()
    {
        return $this->status .' '. $this->additional_status;
    }

    /**
     * Relations
     */

    public function transactions()
    {
        return $this->hasMany(InvoiceTransaction::class,'invoice_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function scopeOrders($query)
    {
        return $query->where('payable_type','=','Order');
    }

    public function scopeDepositWallets($query)
    {
        return $query->where('payable_type','=','DepositWallet');
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid','=',true);
    }

    public function scopeNotPaid($query)
    {
        return $query->where('is_paid','=',false);
    }

    public function scopePaidOver($query)
    {
        return $query->where('additional_status','=','PaidOver');
    }

    public function scopePaidOverNotRefunded($query)
    {
        return $query->where('additional_status','=','PaidOver')->whereNull('is_refund_at');
    }

    public function scopeRefunded($query)
    {
        return $query->whereNotNull('is_refund_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_time','<',now()->toDateTimeString());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiration_time','>',now()->toDateTimeString());
    }

    public function refunder()
    {
        return $this->belongsTo(User::class,'refunder_user_id','id');
    }

    /**
     * Mutators
     */

    public function getTypeAttribute()
    {
        switch($this->attributes['payable_type']) {
            case 'Order':
                return 'order';
                break;
            case 'DepositWallet' :
                return 'wallet';
                break;
            default:
                return 'Unknown';
                break;
        }
    }

    /**
     * Methods
     */
    public function getInvoiceService()
    {
        $invoice_service = app(\Payments\Services\Invoice::class);
        $invoice_service->setPayableType((string)$this->attributes['payable_type']);
        $invoice_service->setPayableId((int)$this->attributes['payable_id']);
        $invoice_service->setUserId((int)$this->attributes['user_id']);
        $invoice_service->setPfAmount((double)$this->attributes['pf_amount']);
        $invoice_service->setAmount((double)$this->attributes['amount']);
        $invoice_service->setTransactionsId((string)$this->attributes['transaction_id']);
        $invoice_service->setCheckoutLink((string)$this->attributes['checkout_link']);
        $invoice_service->setStatus((string)$this->attributes['status']);
        $invoice_service->setAdditionalStatus((string)$this->attributes['additional_status']);

        $invoice_service->setIsPaid((boolean)$this->attributes['is_paid']);

        $invoice_service->setPaidAmount((double)$this->attributes['paid_amount']);
        $invoice_service->setDueAmount((double)$this->attributes['due_amount']);
        $invoice_service->setDepositAmount((double)$this->attributes['deposit_amount']);
        $invoice_service->setExpirationTime((string)$this->attributes['expiration_time']);

        $invoice_service->setPaymentType((string)$this->attributes['payment_type']);
        $invoice_service->setPaymentCurrency((string)$this->attributes['payment_currency']);
        $invoice_service->setPaymentDriver((string)$this->attributes['payment_driver']);

        $invoice_service->setCreatedAt((string)$this->attributes['created_at']);
        $invoice_service->setUpdatedAt((string)$this->attributes['updated_at']);

        $invoice_service->setUser($this->user->getUserService());
        return $invoice_service;

    }
}
