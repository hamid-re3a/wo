<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Packages\Models\CategoryHistory
 *
 * @property int $id
 * @property int $legacy_id
 * @property string $key
 * @property string $name
 * @property string $short_name
 * @property int $roi_percentage
 * @property int $direct_percentage
 * @property int $binary_percentage
 * @property int $package_validity_in_days
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereBinaryPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereDirectPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereLegacyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory wherePackageValidityInDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereRoiPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CategoryHistory extends Model
{
    use HasFactory;
    protected $guarded = [];
}
