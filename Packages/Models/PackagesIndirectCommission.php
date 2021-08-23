<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Packages\Models\PackagesIndirectCommission
 *
 * @property int $id
 * @property int $package_id
 * @property int $level
 * @property int $percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission query()
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|PackagesIndirectCommission wherePackageId($value)
 * @property-read \Packages\Models\Package $package
 */
class PackagesIndirectCommission extends Model
{
    use HasFactory;

    protected $table = 'packages_indirect_commissions';

    protected $guarded = [];

    /**
     * relation with package
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
