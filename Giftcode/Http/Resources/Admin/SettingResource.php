<?php

namespace Giftcode\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SettingResource extends JsonResource
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
            'name' => $this->name,
            'value' => $this->value,
            'title' => $this->title,
            'description' => $this->description
        ];
    }
}
