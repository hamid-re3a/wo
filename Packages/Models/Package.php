<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Packages\Models\Package
 *
 * @property int $id
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
 * @method static \Illuminate\Database\Eloquent\Builder|Package newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Package newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Package query()
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereBinaryPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereDirectPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereRoiPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereValidityInDays($value)
 * @mixin \Eloquent
 * @property-read \Packages\Models\Category $Category
 * @property-read \Illuminate\Database\Eloquent\Collection|\Packages\Models\PackagesIndirectCommission[] $packageIndirectCommission
 * @property-read int|null $package_indirect_commission_count
 */
class Package extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * relation with Category
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Category()
    {
        return $this->belongsTo(Category::class)->with('categoryIndirectCommission');
    }

    /**
     * relation with packageIndicatorCommission
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function packageIndirectCommission()
    {
        return $this->hasMany(PackagesIndirectCommission::class);
    }

    /**
     * Methods
     */
    public function getPackageService()
    {
        $package_service = new \Packages\Services\Package();
        $package_service->setId($this->attributes['id']);
        $package_service->setName($this->attributes['name']);
        $package_service->setShortName($this->attributes['short_name']);
        $package_service->setValidityInDays($this->attributes['validity_in_days']);
        $package_service->setPrice($this->attributes['price']);
        $package_service->setRoiPercentage($this->attributes['roi_percentage']);
        $package_service->setDirectPercentage($this->attributes['direct_percentage']);
        $package_service->setBinaryPercentage($this->attributes['binary_percentage']);
        $package_service->setCategoryId($this->attributes['category_id']);
        $package_service->setDeletedAt($this->attributes['deleted_at']);
        $package_service->setCreatedAt($this->attributes['created_at']);
        $package_service->setUpdatedAt($this->attributes['updated_at']);

        return $package_service;

    }
}
