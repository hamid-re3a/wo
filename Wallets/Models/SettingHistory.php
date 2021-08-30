<?php

namespace Wallets\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Wallets\Models\SettingHistories
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
        'title',
        'description'
    ];

    protected $casts = [
        'setting_id' => 'integer',
        'actor_id' => 'integer',
        'name' => 'string',
        'value' => 'string',
    ];

    protected $table = 'wallet_setting_histories';

}
