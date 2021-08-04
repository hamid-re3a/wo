<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\PaymentDriverHistory
 *
 * @property int $id
 * @property int $legacy_id
 * @property string $name
 * @property int $is_active
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory whereLegacyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriverHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentDriverHistory extends Model
{
    use HasFactory;
}
