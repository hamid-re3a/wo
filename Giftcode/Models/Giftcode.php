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
    use CodeGenerator;

    protected $fillable = [
        'user_id',
        'package_id',
        'code',
        'expiration_date',
        'redeem_date',
        'redeem_user_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'package_id' => 'integer',
        'code' => 'string',
        'expiration_date' => 'datetime',
        'redeem_date' => 'datetime',
        'redeem_user_id' => 'integer'
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

    /**
     * Auto fill code field for new giftcode
     */
    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value;

        list($code,$expirationDate) = $this->generateCode();

        $this->attributes['code'] = $code;
        $this->attributes['expiration_date'] = $expirationDate;
    }





}
