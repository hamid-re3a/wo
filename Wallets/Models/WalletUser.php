<?php

namespace Wallets\Models;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Orders\Models\WalletUser
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletUser whereUsername($value)
 * @mixin \Eloquent
 * @property-read mixed $full_name
 */
class WalletUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getFullNameAttribute()
    {
        return ucwords(strtolower($this->first_name . ' ' . $this->last_name));
    }


}
