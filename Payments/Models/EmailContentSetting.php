<?php

namespace Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payments\Models\EmailAndTextSetting
 *
 * @property int $id
 * @property string $key
 * @property string $subject
 * @property string $from
 * @property string $from_name
 * @property string $body
 * @property int $variables_number
 * @property string $variables_description
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereVariablesDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereVariablesNumber($value)
 * @mixin \Eloquent
 * @property string $variables
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereVariables($value)
 * @property int $is_active
 * @method static \Illuminate\Database\Eloquent\Builder|EmailContentSetting whereIsActive($value)
 */
class EmailContentSetting extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'payment_email_content_settings';
}
