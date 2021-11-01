<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orders\Models\Order;
use Packages\Services\Grpc\IndirectCommission;

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
 * @property-read \Packages\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\Packages\Models\PackagesIndirectCommission[] $packageIndirectCommission
 * @property-read int|null $package_indirect_commission_count
 */
class Package extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];

    /**
     * relation with Category
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->with('categoryIndirectCommission');
    }


    public function packageIndirectCommission()
    {
        return $this->hasMany(PackagesIndirectCommission::class);
    }

    /**
     * relation with Order
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Methods
     */
    public function getGrpcMessage()
    {
        $package_service = new \Packages\Services\Grpc\Package();
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
        $package_service->setIndirectCommission($this->mapIndirectCommissions());

        return $package_service;

    }

    private function mapIndirectCommissions()
    {
        if ($this->packageIndirectCommission->count() > 0) {
            $indirect_commissions = $this->packageIndirectCommission;
        } else {
            $indirect_commissions = $this->category->categoryIndirectCommission;
        }
        $data_array = $indirect_commissions->map(function ($item) {
            $indirect_commission = new IndirectCommission();
            $indirect_commission->setLevel($item->level);
            $indirect_commission->setPercentage($item->percentage);
            return $indirect_commission;
        });
        return $data_array->toArray();
    }
}
