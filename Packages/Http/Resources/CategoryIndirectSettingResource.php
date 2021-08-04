<?php

namespace Packages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryIndirectSettingResource extends JsonResource
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
