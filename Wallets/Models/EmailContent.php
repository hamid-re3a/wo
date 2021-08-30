<?php

namespace Wallets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Wallets\Models\EmailContent
 *
 * @property int $id
 * @property string $key
 * @property boolean $is_active
 * @property string $subject
 * @property string $from
 * @property string $from_name
 * @property string $body
 * @property string $variables
 * @property string $variables_description
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
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

    protected $table = 'wallet_email_contents';

    public function histories()
    {
        return $this->hasMany(EmailContentHistory::class,'email_id','id');
    }

}
