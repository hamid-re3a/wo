<?php

namespace ApiGatewayUser\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\Models\Category;

class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $category = CategoryResource::find($this->category_id);
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'short_name'=>$this->short_name,
            'validity_in_days'=> !is_null($this->validity_in_days) ? Category::query()->find($this->category_id)->validity_in_days : null,
            'price'=>$this->price,
            'roi_percentage'=> !is_null($this->roi_percentage) ? Category::query()->find($this->category_id)->roi_percentage : null,
            'direct_percentage'=> !is_null($this->direct_percentage) ? Category::query()->find($this->category_id)->direct_percentage : null,
            'binary_percentage'=> !is_null($this->binary_percentage) ? Category::query()->find($this->category_id)->binary_percentage : null,
            'category' => !is_null($this->category_id) ? CategoryResource::make(Category::query()->find($this->category_id)) : null,
        ];
    }
}
