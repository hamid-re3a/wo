<?php
namespace User\Models;

use Giftcode\Models\Giftcode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orders\Models\Order;
use Spatie\Permission\Traits\HasRoles;

/**
 * User\Models\User
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @mixin \Eloquent
 * @property-read mixed $full_name
 */
class User extends Model
{
    protected $table = "users";
    protected $fillable = [
        "id",
        "first_name",
        "last_name",
        "email",
        "username",
    ];

    use HasFactory, HasRoles;

    Protected $guard_name ='api';


    public function getFullNameAttribute()
    {
        return ucwords(strtolower($this->first_name . ' ' . $this->last_name));
    }

    public function giftCodes()
    {
        return $this->hasMany(Giftcode::class,'user_id','id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'user_id','id');
    }

    public function paidOrders()
    {
        return $this->hasMany(Order::class,'user_id','id')->whereNotNull('is_paid_at');
    }

}
