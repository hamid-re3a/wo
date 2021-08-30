<?php

namespace Wallets\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

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
