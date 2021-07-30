<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\PaymentCurrency
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentCurrency whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentCurrency extends Model
{
    use HasFactory;
}
