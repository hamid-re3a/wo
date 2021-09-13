<?php

namespace Wallets\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailContentResource extends JsonResource
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
            'key' => $this->key,
            'is_active' => $this->is_active,
            'subject' => $this->subject,
//            'from' => $this->from,
//            'from_name' => $this->from_name,
            'body' => $this->body,
//            'variables' => $this->variables,
//            'variables_description' => $this->variables_description,
//            'type' => $this->type,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp
        ];
    }
}
