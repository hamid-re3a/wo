<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\PaymentTypeHistory
 *
 * @property int $id
 * @property int $legacy_id
 * @property string $name
 * @property int $is_active
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory whereLegacyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentTypeHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentTypeHistory extends Model
{
    use HasFactory;
}
