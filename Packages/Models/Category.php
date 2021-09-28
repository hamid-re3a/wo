<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Packages\Models\Category
 *
 * @property int $id
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
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereBinaryPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDirectPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePackageValidityInDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereRoiPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Packages\Models\CategoriesIndirectCommission[] $categoryIndirectCommission
 * @property-read int|null $category_indirect_commission_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Packages\Models\Package[] $packages
 * @property-read int|null $packages_count
 */
class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * relation with package
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }


    public function categoryIndirectCommission()
    {
        return $this->hasMany(CategoriesIndirectCommission::class);
    }

}
