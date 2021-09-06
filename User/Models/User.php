<?php

namespace User\Models;

use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;
use Giftcode\Models\Giftcode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Orders\Models\Order;
use Payments\Models\Invoice;
use Spatie\Permission\Traits\HasRoles;
use User\database\factories\UserFactory;

/**
 * User\Models\User
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $block_type
 * @property boolean|null $is_freeze
 * @property boolean|null $is_deactivate
 * @property integer|null $member_id
 * @property integer|null $sponsor_id
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
class User extends Model implements WalletFloat
{
    use HasWalletFloat, HasWallets, HasFactory, HasRoles;
    protected $table = "users";
    protected $fillable = [
        "id",
        "first_name",
        "last_name",
        "email",
        "username",
        'member_id',
        'sponsor_id',
        'is_deactivate',
        'is_freeze',
        'block_type',
    ];

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    Protected $guard_name = 'api';

    /**
     * Mutators
     */

    public function getFullNameAttribute()
    {
        return ucwords(strtolower($this->first_name . ' ' . $this->last_name));
    }

    /**
     * Relations
     */

    public function transactions(): MorphMany
    {
        return $this->morphMany(config('wallet.transaction.model', \Bavix\Wallet\Models\Transaction::class), 'payable');
    }

    public function giftCodes()
    {
        return $this->hasMany(Giftcode::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function paidOrders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id')->whereNotNull('is_paid_at');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'user_id', 'id');
    }


    /**
     * Methods
     */
    public function getUserService()
    {
        $this->fresh();
        $user = new \User\Services\User();
        $user->setId($this->attributes['id']);
        $user->setFirstName($this->attributes['first_name']);
        $user->setLastName($this->attributes['last_name']);
        $user->setUsername($this->attributes['username']);
        $user->setEmail($this->attributes['email']);
        $user->setMemberId($this->attributes['member_id']);

        if (isset($this->attributes['sponsor_id']) AND !empty($this->attributes['sponsor_id']))
            $user->setSponsorId($this->attributes['sponsor_id']);

        if (isset($this->attributes['block_type']) AND !empty($this->attributes['block_type']))
            $user->setBlockType($this->attributes['block_type']);

        if (isset($this->attributes['is_deactivate']))
            $user->setIsDeactivate($this->attributes['is_deactivate']);

        if (isset($this->attributes['is_freeze']))
            $user->setIsFreeze($this->attributes['is_freeze']);

        if ($this->getRoleNames()->count()) {
            $role_name = implode(",", $this->getRoleNames()->toArray());
            $user->setRole($role_name);
        }

        return $user;
    }

}
