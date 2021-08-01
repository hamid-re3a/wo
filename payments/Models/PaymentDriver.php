<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\PaymentDriver
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentDriver whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentDriver extends Model
{
    use HasFactory;
}
