<?php

namespace Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Orders\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $to_user_id
 * @property int $total_cost_in_usd
 * @property int $packages_cost_in_usd
 * @property int $registration_fee_in_usd
 * @property string|null $is_paid_at
 * @property string|null $is_resolved_at
 * @property string|null $is_refund_at
 * @property string|null $is_expired_at
 * @property string|null $is_commission_resolved_at
 * @property string $payment_type
 * @property string $payment_currency
 * @property string|null $payment_driver
 * @property string $plan
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsCommissionResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsPaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsRefundAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereIsResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePackagesCostInUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRegistrationFeeInUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereToUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalCostInUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Orders\Models\OrderUser $user
 * @property-read \Orders\Models\OrderUser $toUser
 */
class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(OrderUser::class,'user_id','id');
    }


    public function toUser()
    {
        return $this->belongsTo(OrderUser::class,'user_id','id');
    }
}
