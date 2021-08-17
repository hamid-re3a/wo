<?php

namespace Giftcode\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Giftcode\Models\Giftcode
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property double $price
 */
class Setting extends Model
{
    protected $fillable = [
        'name',
        'value',
    ];

    protected $casts = [
        'name' => 'string',
        'value' => 'string',
    ];

    protected $table = 'Giftcodesettings';


    /**
     * Relations
     */
    public function histories()
    {
        return $this->hasMany(SettingHistory::class,'setting_id','id');
    }
}
