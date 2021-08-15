<?php

namespace Packages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Packages\Models\PackageUser
 *
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $username
 * @property string|null $email
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PackageUser whereUsername($value)
 * @mixin \Eloquent
 * @property-read mixed $full_name
 */
class PackageUser extends Model
{
    use HasFactory;

    protected $table = "package_users";
    protected $guarded = [];
}
