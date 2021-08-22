<?php

namespace Giftcode\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Giftcode\Models\Giftcode
 *
 * @property int $id
 */
class EmailContent extends Model
{
    protected $fillable = [
        'key',
        'is_active',
        'subject',
        'from',
        'from_name',
        'body',
        'variables',
        'variables_description',
        'type'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'giftcode_email_contents';

    public function histories()
    {
        return $this->hasMany(EmailContentHistory::class,'email_id','id');
    }

}
