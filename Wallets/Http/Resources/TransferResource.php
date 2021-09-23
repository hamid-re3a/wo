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
        $fee = null;
        if(!empty($this->withdraw->meta) AND array_key_exists('fee', $this->withdraw->meta))
            $fee = $this->withdraw->meta['fee'];

        return [
            'id' => $this->member_id,
            'to_member_id' => $this->deposit->payable->member_id,
            'from' => [
                'transaction_id' => $this->deposit->uuid,
                'wallet' => $this->from->name,
//                'confirmed' => $this->deposit->confirmed
            ],
            'to' => [
                'transaction_id' => $this->withdraw->uuid,
                'wallet' => $this->to->name,
//                'confirmed' => $this->withdraw->confirmed
            ],
            'amount' => walletPfAmount($this->deposit->amountFloat),
            'fee' =>  walletPfAmount($fee),
            'created_at' => $this->created_at->timestamp
        ];
    }
}
