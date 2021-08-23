<?php

namespace Payments\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\Invoice
 *
 * @property int $id
 * @property int|null $order_id
 * @property int $amount
 * @property string|null $transaction_id
 * @property string|null $checkout_link
 * @property string|null $status
 * @property string $additional_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $full_status
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
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDueAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereIsPaid($value)
 * @property string|null $expiration_time
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereExpirationTime($value)
 * @property float $pf_amount
 * @property string $payment_type
 * @property string $payment_currency
 * @property string|null $payment_driver
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePfAmount($value)
 */
class Invoice extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getFullStatusAttribute()
    {
        return $this->status .' '. $this->additional_status;
    }
}
