<?php

namespace Giftcode_\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Giftcode\Models\GiftcodeUser
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $full_name
 */
class GiftcodeUser extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'email'
    ];

    protected $table = 'giftcode_users';

    public function getFullNameAttribute()
    {
        return ucwords(strtolower($this->first_name . ' ' . $this->last_name));
    }

    public function giftCodes()
    {
        return $this->hasMany(Giftcode::class,'user_id','id');
    }

}
