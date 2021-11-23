<?php

namespace Packages\Convert\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionPackage extends Model
{
    protected $guarded = [];

    protected $table = '2297_package';
    protected $connection = 'conversion_mysql';
}
