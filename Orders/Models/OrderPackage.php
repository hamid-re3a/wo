<?php

namespace Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Orders\Models\OrderPackage
 *
 * @property int $id
 * @property int $order_id
 * @property int $package_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderPackage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderPackage extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id','id');
    }
}
