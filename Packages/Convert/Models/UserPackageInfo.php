<?php

namespace Packages\Convert\Models;

use Illuminate\Database\Eloquent\Model;

class UserPackageInfo extends Model
{
    protected $guarded = [];

    protected $table = 'sajid_reports';
    protected $primaryKey = 'user_id';
    protected $connection = 'conversion_mysql';

    public function lastPackage()
    {
        return $this->hasOne(ConversionPackage::class,'product_id','highest_package');
    }
}
