<?php

namespace Wallets\Http\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use User\Models\User;

class EarningWalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {


        return [
            'name' => $this->name,
            'balance' => $this->balanceFloat,
            'transactions_count' => (int) $this->transactions->where('wallet_id',$this->id)->count(),
            'transfers_count' => (int) $this->transfers->count(),
        ];
    }
}
