<?php

namespace Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use User\Models\User;

/**
 * Wallets\Models\Giftcode
 *
 * @property int $id
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
