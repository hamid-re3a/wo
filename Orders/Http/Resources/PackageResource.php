<?php

namespace Orders\Http\Resources;

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
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['name'],
            'short_name' => $this->resource['short_name'],
            'expire_date' => $this->resource['expire_date'],

        ];
    }
}
