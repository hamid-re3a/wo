<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\PaymentCurrencyHistory
 *
 * @property int $id
 * @property int $legacy_id
 * @property string $name
 * @property int $is_active
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory whereLegacyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrencyHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentCurrencyHistory extends Model
{
    use HasFactory;
}
