<?php

namespace Giftcode\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Giftcode\Models\Package
 *
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $validity_in_days
 * @property double $price
 */
class Package extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'validity_in_days',
        'price'
    ];

    protected $casts = [
        'name' => 'string',
        'short_name' => 'string',
        'validity_in_days' => 'integer',
        'price' => 'double',
    ];

    protected $table = 'giftcode_packages';

    public function giftcodes()
    {
        return $this->belongsTo(Package::class,'package_id','id');
    }

    /**
     * Methods
     */
    public function getGrpcMessage()
    {
        $package_service = new \Giftcode\Services\Grpc\Package();
        $package_service->setId($this->attributes['id']);
        $package_service->setName($this->attributes['name']);
        $package_service->setShortName($this->attributes['short_name']);
        if($this->attributes['validity_in_days'])
            $package_service->setValidityInDays($this->attributes['validity_in_days']);
        if($this->attributes['price'])
            $package_service->setPrice($this->attributes['price']);
        if(isset($this->attributes['deleted_at']))
            $package_service->setDeletedAt($this->attributes['deleted_at']);
        if($this->attributes['created_at'])
            $package_service->setCreatedAt($this->attributes['created_at']);
        if($this->attributes['updated_at'])
            $package_service->setUpdatedAt($this->attributes['updated_at']);

        return $package_service;

    }


}
