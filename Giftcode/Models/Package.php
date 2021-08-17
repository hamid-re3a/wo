<?php

namespace Giftcode\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Giftcode\Models\Giftcode
 *
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $validity_in_days
 * @property double $price
 */
class Package extends Model
{
    protected $fillable = [
        'package_id',
        'name',
        'short_name',
        'validity_in_days',
        'price'
    ];

    protected $casts = [
        'package_id',
        'name' => 'string',
        'short_name' => 'string',
        'validity_in_days' => 'integer',
        'price' => 'double',
    ];

    protected $table = 'Giftcodepackages';

    public function giftcodes()
    {
        return $this->belongsTo(Package::class,'package_id','id');
    }


}
