<?php

namespace Wallets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use User\Models\User;

/**
 * Wallets\Models\EmailContentHistory
 *
 * @property int $id
 * @property int $email_id
 * @property int $actor_id
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
class EmailContentHistory extends Model
{
    protected $fillable = [
        'email_id',
        'actor_id',
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

    protected $table = 'wallet_email_content_histories';

    public function email()
    {
        return $this->belongsTo(EmailContent::class,'email_id','id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }


}
