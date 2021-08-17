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
class SettingHistory extends Model
{
    protected $fillable = [
        'setting_id',
        'actor_id',
        'name',
        'value',
    ];

    protected $casts = [
        'setting_id' => 'integer',
        'actor_id' => 'integer',
        'name' => 'string',
        'value' => 'string',
    ];

    protected $table = 'giftcode_setting_histories';

}
