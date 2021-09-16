<?php

namespace Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Orders\Models\OrderHistory
 *
 * @property int $id
 * @property int $legacy_id
 * @property int $user_id
 * @property int|null $from_user_id
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereIsCommissionResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereIsExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereIsPaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereIsRefundAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereIsResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereLegacyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory wherePackagesCostInUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory wherePaymentCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory wherePaymentDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereRegistrationFeeInUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereFromUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereTotalCostInUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderHistory whereUserId($value)
 * @mixin \Eloquent
 */
class OrderHistory extends Model
{
    use HasFactory;
}
