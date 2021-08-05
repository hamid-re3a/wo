<?php

namespace Packages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\Models\CategoriesIndirectCommission;

class CategoryResource extends JsonResource
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
            'id'=>$this->id,
            'key'=>$this->key,
            'name'=>$this->name,
            'short_name'=>$this->short_name,
            'roi_percentage'=>$this->roi_percentage,
            'direct_percentage'=>$this->direct_percentage,
            'binary_percentage'=>$this->binary_percentage,
            'package_validity_in_days'=>$this->package_validity_in_days,
            'indirect_percentages'=> $this->categoryIndirectCommission ? CategoryIndirectSettingResource::collection($this->categoryIndirectCommission): null
        ];
    }
}
