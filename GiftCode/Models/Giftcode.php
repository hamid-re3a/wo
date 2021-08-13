<?php

namespace Giftcode\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Giftcode\Models\Giftcode
 *
 * @property int $id
 * @property int $user_id
 * @property boolean $is_used
 * @property string|null $used_date
 * @property int $used_user_id
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $full_name
 */
class Giftcode extends Model
{

    protected $fillable = [
        'user_id',
        'is_used',
        'used_date',
        'used_user_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_used' => 'boolean',
        'used_date' => 'datetime',
        'used_user_id' => 'integer'
    ];

    protected $table = 'giftcodes';

    /**
     * Relation
     */
    public function creator()
    {
        return $this->belongsTo(GiftcodeUser::class,'user_id','id');
    }



}
