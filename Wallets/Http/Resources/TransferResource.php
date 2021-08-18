<?php

namespace Wallets\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
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
            'id' => $this->uuid,
            'from' => [
                'transaction_id' => $this->deposit->uuid,
                'wallet' => $this->from->name,
                'confirmed' => $this->deposit->confirmed
            ],
            'to' => [
                'transaction_id' => $this->withdraw->uuid,
                'wallet' => $this->to->name,
                'confirmed' => $this->withdraw->confirmed
            ],
            'amount' => number_format($this->deposit->amountFloat,2)
        ];
    }
}
