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
 * @property string $code
 * @property string $package_name
 * @property boolean $is_used
 * @property string| $redeemer_full_name
 * @property string| $creator_full_name
 * @property int $redeem_user_id
 * @property int $packages_cost_in_usd
 * @property int $registration_fee_in_usd
 * @property int $total_cost_in_usd
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
    public function creator()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function redeemer()
    {
        return $this->belongsTo(User::class,'redeem_user_id','id');
    }

    /**
     * Mutators
     */

    public function getPackageNameAttribute()
    {
        if($this->package()->exists() AND !is_null($this->package->name))
            return $this->package->name;

        return null;
    }

    public function getRedeemerFullNameAttribute()
    {
        if($this->redeemer()->exists() AND !is_null($this->redeemer->first_name))
            return $this->redeemer->full_name;

        return null;
    }

    public function getCreatorFullNameAttribute()
    {
        if($this->creator()->exists() AND !is_null($this->creator->first_name))
            return $this->creator->full_name;

        return null;
    }


    /**
     * Methods
     */
    public function getRefundAmount()
    {
        //Check we should calculate cancelation fee or not
        if(!giftcodeGetSetting('include_cancellation_fee'))
            return $this->total_cost_in_usd;

        //Calculate cancelation fee in fiat (USD)
        $cancelation_fee_in_percent = giftcodeGetSetting('cancellation_fee');
        $cancelation_fee_in_fiat = ($this->total_cost_in_usd * $cancelation_fee_in_percent) / 100 ;
        return $this->total_cost_in_usd - $cancelation_fee_in_fiat;
    }



}
