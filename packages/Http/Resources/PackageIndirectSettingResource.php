<?php

namespace Packages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageIndirectSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'level'=>$this->level,
            'percentage'=>$this->percentage,
        ];
    }
}
