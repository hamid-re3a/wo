<?php

namespace Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Orders\Models\OrderUser
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereUsername($value)
 * @mixin \Eloquent
 * @property-read mixed $full_name
 */
class OrderUser extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getFullNameAttribute()
    {
        return ucwords(strtolower($this->first_name . ' ' . $this->last_name));
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'user_id','id');
    }

    public function paidOrders()
    {
        return $this->orders()->whereNotNull('is_paid_at');
    }

    public function pendingOrders()
    {
        return $this->orders()->whereNull('is_paid_at');
    }

    public function refundedOrders()
    {
        return $this->orders()->whereNotNull('is_resolved_at');
    }
}
