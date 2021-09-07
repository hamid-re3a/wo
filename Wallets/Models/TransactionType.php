<?php

namespace Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use User\Models\User;

/**
 * Wallets/Models/TransactionType
 *
 * @property int $id
 * @property int $parent_id
 * @property string|null $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read TransactionType $parent
 * @property-read TransactionType $subTypes
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @mixin \Eloquent
 * @property-read mixed $full_name
 */

class TransactionType extends Model
{
    protected $table = 'wallet_transaction_types';

    protected $fillable = [
        'parent_id',
        'name',
        'description'
    ];

    public function parent()
    {
        return $this->belongsTo(TransactionType::class,'parent_id','id');
    }

    public function subTypes()
    {
        return $this->hasMany(TransactionType::class,'parent_id','id');
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class,TransactionMetaData::class);
    }

}
