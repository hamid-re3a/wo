<?php

namespace Wallets\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CharityWalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        $metaData = $this->metaData->first();

        return [
            'id' => $this->uuid,
            'order_id' => array_key_exists('order_id',$this->meta) ? $this->meta['order_id'] : null,
//            'type' => $metaData ? $metaData->name : $this->type,
            'type' => $this->type,
            'member_id' => array_key_exists('member_id',$this->meta) ? $this->meta['member_id'] : null,
            'package_name' => array_key_exists('package_name',$this->meta) ? $this->meta['package_name'] : null,
            'amount' => (double)$this->amountFloat,
            'before_balance' => $metaData ? (double)$metaData->pivot->wallet_before_balance : 0,
            'after_balance' => $metaData ? (double)$metaData->pivot->wallet_after_balance : 0,
            'remarks' => array_key_exists('remarks',$this->meta) ? $this->meta['remarks'] : null,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
