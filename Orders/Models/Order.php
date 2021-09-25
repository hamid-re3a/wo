<?php

namespace Orders\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orders\Services\Grpc\OrderPlans;
use Packages\Services\Grpc\Id;
use Packages\Services\PackageService;
use User\Models\User;

/**
 * Orders\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $from_user_id
 * @property int $total_cost_in_usd
 * @property int $packages_cost_in_usd
 * @property int $registration_fee_in_usd
 * @property int $validity_in_days
 * @property string|null $is_paid_at
 * @property string|null $is_resolved_at
 * @property string|null $is_refund_at
 * @property string|null $is_commission_resolved_at
 * @property string|null $expires_at
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
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereFromUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalCostInUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Orders\Models\User $user
 * @property-read \Orders\Models\User $fromUser
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
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }

    public function refreshOrder()
    {
        $this->reCalculateCosts();
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
        $package_service_object = app(PackageService::class)->packageById($id);
        return $package_service_object->getPrice();
    }


    /*
     * Scopes
     */
    public function scopeFilter($query)
    {
        if (request()->has('is_paid_at_from'))
            $query->whereDate('is_paid_at', ' >', request()->get('is_paid_at_from'));

        if (request()->has('is_paid_at_to'))
            $query->whereDate('is_paid_at', ' <', request()->get('is_paid_at_to'));

        if (request()->has('created_at_from'))
            $query->whereDate('created_at', ' >', request()->get('created_at_from'));

        if (request()->has('created_at_to'))
            $query->whereDate('created_at', ' <', request()->get('created_at_to'));

        if (request()->has('is_paid') AND request()->get('is_paid'))
            $query->whereNotNull('is_paid_at');

        if (request()->has('is_refunded') AND request()->get('is_refunded'))
            $query->whereNotNull('is_refunded');

        if (request()->has('is_expired') AND request()->get('is_expired'))
            $query->whereNotNull('is_expired');

        return $query;
    }

    /*
     * Methods
     */
    public function getOrderService()
    {
        $order_service = new \Orders\Services\Grpc\Order();
        $order_service->setId((int)$this->attributes['id']);
        $order_service->setUserId((int)$this->attributes['user_id']);

        $order_service->setFromUserId((int)$this->attributes['from_user_id']);

        $order_service->setTotalCostInUsd((float)$this->attributes['total_cost_in_usd']);
        $order_service->setPackagesCostInUsd((float)$this->attributes['packages_cost_in_usd']);
        $order_service->setRegistrationFeeInUsd((float)$this->attributes['registration_fee_in_usd']);

        $order_service->setIsPaidAt((string)$this->attributes['is_paid_at']);
        $order_service->setIsResolvedAt((string)$this->attributes['is_resolved_at']);
        $order_service->setIsRefundAt((string)$this->attributes['is_refund_at']);
        $order_service->setIsCommissionResolvedAt((string)$this->attributes['is_commission_resolved_at']);

        $order_service->setPaymentType((string)$this->attributes['payment_type']);

        $order_service->setPaymentCurrency((string)$this->attributes['payment_currency']);

        $order_service->setPaymentDriver((string)$this->attributes['payment_driver']);
        $order_service->setPackageId((int)$this->attributes['package_id']);

        $order_service->setPlan((int)OrderPlans::value($this->attributes['plan']));
        $order_service->setValidityInDays((int)$this->attributes['validity_in_days']);


        $order_service->setDeletedAt((string)$this->attributes['deleted_at']);
        $order_service->setCreatedAt((string)$this->attributes['created_at']);
        $order_service->setUpdatedAt((string)$this->attributes['updated_at']);

        return $order_service;
    }

    /*
     * Mutators
     */
    public function setIsPaidAtAttribute($value)
    {
        $this->attributes['is_paid_at'] = $value;
        if (
            isset($this->attributes['validity_in_days']) AND isset($this->attributes['is_paid_at'])
            AND !empty($this->attributes['is_paid_at']) AND !empty($this->attributes['validity_in_days'])
        )
            $this->attributes['expires_at'] = Carbon::make($value)->addDays($this->attributes['validity_in_days'])->toDateTimeString();
    }


}
