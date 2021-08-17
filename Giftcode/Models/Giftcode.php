<?php

namespace Giftcode_\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Giftcode\Models\Giftcode
 *
 * @property int $id
 * @property int $user_id
 * @property int $package_id
 * @property string $code
 * @property boolean $is_used
 * @property string|null $used_date
 * @property int $used_user_id
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Giftcode extends Model
{

    protected $fillable = [
        'user_id',
        'package_id',
        'code',
        'redeem_date',
        'redeem_user_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'package_id' => 'integer',
        'code' => 'string',
        'redeem_date' => 'datetime',
        'redeem_user_id' => 'integer'
    ];

    protected $table = 'giftcodes';

    /**
     * Relation
     */
    public function creator()
    {
        return $this->belongsTo(GiftcodeUser::class,'user_id','id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function redeemer()
    {
        return $this->belongsTo(GiftcodeUser::class,'redeem_user_id','id');
    }




}
