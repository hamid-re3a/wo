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
        'title',
        'description'
    ];

    protected $casts = [
        'name' => 'string',
        'value' => 'string',
        'title' => 'string',
        'description' => 'string',
    ];

    protected $table = 'giftcode_settings';


    /**
     * Relations
     */
    public function histories()
    {
        return $this->hasMany(SettingHistory::class,'setting_id','id');
    }
}
