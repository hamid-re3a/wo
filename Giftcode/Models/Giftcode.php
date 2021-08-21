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
 * @property string|null $used_date
 * @property int $used_user_id
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $expiration_date
 * @property-read Package $package
 * @property-read User $creator
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
        'total_cost_in_usd'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'package_id' => 'integer',
        'code' => 'string',
        'expiration_date' => 'datetime',
        'redeem_date' => 'datetime',
        'redeem_user_id' => 'integer'
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





}
