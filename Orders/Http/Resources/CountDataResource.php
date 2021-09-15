<?php

namespace Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CountDataResource extends JsonResource
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
            'subscriptions_count' => $this['count'],
        ];
    }
}
