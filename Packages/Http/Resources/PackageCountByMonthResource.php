<?php

namespace Packages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Packages\Models\CategoriesIndirectCommission;
use Packages\Models\Category;
use Packages\Models\PackagesIndirectCommission;

class PackageCountByMonthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {


        return [
            'time' => $this->created_at->timestamp,
            $this->short_name => $this->count,
        ];
    }
}
