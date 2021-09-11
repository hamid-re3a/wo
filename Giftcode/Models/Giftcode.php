<?php

namespace Giftcode\Models;

use Giftcode\Traits\CodeGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use User\Models\User;

/**
 * Giftcode\Models\Giftcode
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $package_id
 * @property int $order_id
 * @property string $code
 * @property string $package_name
 * @property boolean $is_used
 * @property string| $redeemer_full_name
 * @property string| $creator_full_name
 * @property int $redeem_user_id
 * @property int $packages_cost_in_usd
 * @property int $registration_fee_in_usd
 * @property int $total_cost_in_usd
 * @property boolean $is_canceled
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $expiration_date
 * @property Carbon|null $redeem_date
 * @property-read Package $package
 * @property-read User $creator
 * @property-read User $redeemer
 */
class Giftcode extends Model
{
    use CodeGenerator;

    protected $fillable = [
        'uuid',
        'user_id',
        'package_id',
        'order_id',
        'code',
        'expiration_date',
        'redeem_date',
        'redeem_user_id',
        'packages_cost_in_usd',
        'registration_fee_in_usd',
        'total_cost_in_usd',
        'is_canceled'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'package_id' => 'integer',
        'order_id' => 'integer',
        'code' => 'string',
        'expiration_date' => 'datetime',
        'redeem_date' => 'datetime',
        'redeem_user_id' => 'integer',
        'is_canceled' => 'boolean'
    ];

    protected $hidden = [
        'id'
    ];

    protected $table = 'giftcodes';

    /**
     * Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function redeemer()
    {
        return $this->belongsTo(User::class, 'redeem_user_id', 'id');
    }

    /**
     * Mutators
     */

    public function getPackageNameAttribute()
    {
        if ($this->package()->exists() AND !is_null($this->package->name))
            return $this->package->name;

        return null;
    }

    public function getRedeemerFullNameAttribute()
    {
        if ($this->redeemer()->exists() AND !is_null($this->redeemer->first_name))
            return $this->redeemer->full_name;

        return null;
    }

    public function getCreatorFullNameAttribute()
    {
        if ($this->creator()->exists() AND !is_null($this->creator->first_name))
            return $this->creator->full_name;

        return null;
    }

    public function getStatusAttribute()
    {
        if (isset($this->attributes['redeem_user_id']))
            return 'Used';

        if (isset($this->attributes['is_canceled']) AND $this->attributes['is_canceled'] == true)
            return 'Canceled';
        if (isset($this->attributes['redeem_user_id']) AND $this->expiration_date->isPast())
            return 'Expired';

        return 'Ready to use';
    }


    /**
     * Methods
     */
    public function getRefundAmount()
    {
        //Check we should calculate cancelation fee or not
        if (!giftcodeGetSetting('include_cancellation_fee'))
            return $this->total_cost_in_usd;

        //Calculate cancelation fee in fiat (USD)
        $cancelation_fee_in_percent = giftcodeGetSetting('cancellation_fee');
        $cancelation_fee_in_fiat = ($this->total_cost_in_usd * $cancelation_fee_in_percent) / 100;
        return $this->total_cost_in_usd - $cancelation_fee_in_fiat;
    }


    public function getGiftcodeService()
    {
        $giftcode_service = new \Giftcode\Services\Giftcode();
        $giftcode_service->setId((int)$this->attributes['id']);
        $giftcode_service->setUuid((string)$this->attributes['uuid']);
        $giftcode_service->setUserId((int)$this->attributes['user_id']);
        $giftcode_service->setPackageId((int)$this->attributes['package_id']);
        $giftcode_service->setOrderId((int)$this->attributes['order_id']);

        $giftcode_service->setPackagesCostInUsd((float)$this->attributes['packages_cost_in_usd']);
        $giftcode_service->setRegistrationFeeInUsd((float)$this->attributes['registration_fee_in_usd']);
        $giftcode_service->setTotalCostInUsd((float)$this->attributes['total_cost_in_usd']);

        $giftcode_service->setCode((string)$this->attributes['code']);

        $giftcode_service->setExpirationDate((string)$this->attributes['expiration_date']);

        $giftcode_service->setIsCanceled((boolean)$this->attributes['is_canceled']);

        $giftcode_service->setRedeemUserId((int)$this->attributes['redeem_user_id']);
        $giftcode_service->setRedeemDate((string)$this->attributes['redeem_date']);

        $giftcode_service->setCreatedAt((string)$this->attributes['created_at']);
        $giftcode_service->setUpdatedAt((string)$this->attributes['updated_at']);
        return $giftcode_service;
    }


}
