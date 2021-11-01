<?php

namespace Giftcode\Models;

use Giftcode\Traits\CodeGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use User\Models\User;
use function Symfony\Component\Translation\t;

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
 * @property int $packages_cost_in_pf
 * @property int $registration_fee_in_pf
 * @property int $total_cost_in_pf
 * @property boolean $is_canceled
 * @property boolean $is_expired
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
        'packages_cost_in_pf',
        'registration_fee_in_pf',
        'total_cost_in_pf',
        'is_canceled',
        'is_expired',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'package_id' => 'integer',
        'order_id' => 'integer',
        'code' => 'string',
        'expiration_date' => 'datetime',
        'redeem_date' => 'datetime',
        'redeem_user_id' => 'integer',
        'is_canceled' => 'boolean',
        'is_expired' => 'boolean',
    ];

    protected $with = [
        'user',
        'package',
        'redeemer'
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
        return $this->belongsTo(Package::class, 'package_id', 'id');
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
        $name = null;
        if ($this->package()->exists() AND !is_null($this->package->name)) {
            $name = $this->package->name;
            if ($this->package->short_name)
                $name = $name . ' (' . $this->package->short_name . ')';

        }

        return $name;
    }

    public function getStatusAttribute()
    {
        if (isset($this->attributes['redeem_user_id']))
            return 'Used';

        if (isset($this->attributes['is_canceled']) AND $this->attributes['is_canceled'] == true)
            return 'Canceled';
        if (isset($this->attributes['expiration_date']) AND $this->expiration_date->isPast())
            return 'Expired';

        return 'Unused';
    }


    /**
     * Methods
     */
    public function getRefundAmount()
    {

        $fee = null;
        $type = null;

        if ($this->is_canceled) {
            $fee = giftcodeGetSetting('cancellation_fee');
            $type = giftcodeGetSetting('cancellation_fee_type') == 'fixed' ? 'fixed' : 'percentage';
        } else if ($this->is_expired) {
            $fee = giftcodeGetSetting('expiration_fee');
            $type = giftcodeGetSetting('expiration_fee_type') == 'fixed' ? 'fixed' : 'percentage';
        } else //Check we should calculate cancelation/expiration fee or not
            return $this->total_cost_in_pf;

        if ($type == 'percentage')
            $fee = $this->total_cost_in_pf * $fee / 100;

        //Calculate refundable amount
        return $this->total_cost_in_pf - $fee;
    }


    public function getGrpcMessage()
    {
        $giftcode_service = new \Giftcode\Services\Grpc\Giftcode();
        $giftcode_service->setId((int)$this->attributes['id']);
        $giftcode_service->setUuid((string)$this->attributes['uuid']);
        $giftcode_service->setUserId((int)$this->attributes['user_id']);
        $giftcode_service->setPackageId((int)$this->attributes['package_id']);
        $giftcode_service->setOrderId((int)$this->attributes['order_id']);

        $giftcode_service->setPackagesCostInPf((float)$this->attributes['packages_cost_in_pf']);
        $giftcode_service->setRegistrationFeeInPf((float)$this->attributes['registration_fee_in_pf']);
        $giftcode_service->setTotalCostInPf((float)$this->attributes['total_cost_in_pf']);

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
