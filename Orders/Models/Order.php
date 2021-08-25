<?php

namespace Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Packages\Services\Id;
use Packages\Services\PackageService;
use User\Models\User;

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
 * @property-read \Orders\Models\User $user
 * @property-read \Orders\Models\User $toUser
 * @property-read \Illuminate\Database\Eloquent\Collection|\Orders\Models\OrderPackage[] $packages
 * @property-read int|null $packages_count
 * @method static \Illuminate\Database\Eloquent\Builder|Order filter()
 * @property int $package_id
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePackageId($value)
 */
class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_paid_at' => 'datetime',
        'is_resolved_at' => 'datetime',
        'is_refund_at' => 'datetime',
        'is_expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function toUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function reCalculateCosts()
    {
        $this->refresh();
        if ($this->plan == ORDER_PLAN_START)
            $this->registration_fee_in_usd = (float)getSetting('REGISTRATION_FEE');
        $this->packages_cost_in_usd = (float)$this->orderPackagesPrice();
        $this->total_cost_in_usd = (float)$this->packages_cost_in_usd + (float)$this->registration_fee_in_usd;
        $this->save();
    }

    private function orderPackagesPrice()
    {
        $id = new Id;
        $id->setId($this->package_id);
        $package_service_object = app(PackageService::class )->packageById($id);
        return $package_service_object->getPrice();
    }

    /**
     * Scopes
     */
    public function scopeFilter($query)
    {
        if(request()->has('is_paid_at_from'))
            $query->whereDate('is_paid_at', ' >' , request()->get('is_paid_at_from'));

        if(request()->has('is_paid_at_to'))
            $query->whereDate('is_paid_at', ' <' , request()->get('is_paid_at_to'));

        if(request()->has('created_at_from'))
            $query->whereDate('created_at', ' >' , request()->get('created_at_from'));

        if(request()->has('created_at_to'))
            $query->whereDate('created_at', ' <' , request()->get('created_at_to'));

        if(request()->has('is_paid') AND request()->get('is_paid'))
            $query->whereNotNull('is_paid_at');

        if(request()->has('is_refunded') AND request()->get('is_refunded'))
            $query->whereNotNull('is_refunded');

        if(request()->has('is_expired') AND request()->get('is_expired'))
            $query->whereNotNull('is_expired');

        return $query;
    }
}
