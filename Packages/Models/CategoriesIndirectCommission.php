<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Packages\Models\CategoriesIndirectCommission
 *
 * @property int $id
 * @property int $category_id
 * @property int $level
 * @property int $percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriesIndirectCommission whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Packages\Models\Category $category
 */
class CategoriesIndirectCommission extends Model
{
    use HasFactory;

    protected $table = 'categories_indirect_commissions';

    protected $guarded = [];

    /**
     * relation with category
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
