<?php

namespace Orders\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionCountDataResource extends JsonResource
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
            'subscriptions_count' => $this,
        ];
    }
}
