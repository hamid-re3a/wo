<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Packages\Models\PackageHistory
 *
 * @property int $id
 * @property int $package_id
 * @property string $name
 * @property string $short_name
 * @property int|null $validity_in_days
 * @property float $price
 * @property int|null $roi_percentage
 * @property int|null $direct_percentage
 * @property int|null $binary_percentage
 * @property int $category_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereBinaryPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereDirectPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereLegacyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereRoiPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereValidityInDays($value)
 * @mixin \Eloquent
 * @property int|null $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|PackageHistory whereUserId($value)
 */
class PackageHistory extends Model
{
    use HasFactory;
    protected $guarded = [];
}
