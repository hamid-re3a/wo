<?php

namespace Wallets\Http\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use User\Models\User;

class DepositWalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $total_sum = User::query()->where('id','=',auth()->user()->id)
            ->withSumQuery([
                'transactions.amount AS total_received' => function (Builder $query) {
                    $query->where('wallet_id','=',$this->id);
                    $query->where('type', '=', 'deposit');
                }
            ])
            ->withSumQuery([
                'transactions.amount AS total_spent' => function (Builder $query) {
                    $query->where('wallet_id','=',$this->id);
                    $query->where('type', 'withdraw');

                }
            ])
            ->withSumQuery([
                'transactions.amount AS total_transfer' => function (Builder $query) {
                    $query->where('wallet_id','=',$this->id);
                    $query->where('type','withdraw');
                    $query->whereHas('metaData', function(Builder $subQuery) {
                        $subQuery->where('name','=','Funds transferred');
                    });
                }
            ])
            ->first();
        return [
            'name' => $this->name,
            'balance' => $this->balanceFloat,
            'transactions_count' => (int) $this->transactions->where('wallet_id','=',$this->id)->count(),
            'transfers_count' => (int) $this->transfers->count(),
            'total_transfer' => $total_sum->total_transfer ?$total_sum->total_transfer / 100 : 0,
            'total_received' => $total_sum->total_received ? $total_sum->total_received / 100 : 0,
            'total_spent' => $total_sum->total_spent ? $total_sum->total_spent / 100 : 0
        ];
    }
}
