<?php

namespace Packages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($request->has('package_exactly')) {
            $indirect = PackageIndirectSettingResource::collection($this->packageIndirectCommission);
        } else {
            if ($this->packageIndirectCommission->count() > 0) {
                $indirect = PackageIndirectSettingResource::collection($this->packageIndirectCommission);
            } else {
                $indirect = CategoryIndirectSettingResource::collection($this->category->categoryIndirectCommission);
            }
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'validity_in_days' => is_null($this->validity_in_days) ? $this->category->package_validity_in_days : $this->validity_in_days,
            'price' => $this->price,
            'roi_percentage' => is_null($this->roi_percentage) ? $this->category->roi_percentage : $this->roi_percentage,
            'direct_percentage' => is_null($this->direct_percentage) ? $this->category->direct_percentage : $this->direct_percentage,
            'binary_percentage' => is_null($this->binary_percentage) ? $this->category->binary_percentage : $this->binary_percentage,
            'category' => CategoryResource::make($this->category),
            'indirect_percentages' => $indirect ? $indirect : null
        ];
    }
}
