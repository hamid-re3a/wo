<?php


namespace Wallets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Models\WithdrawProfit;

class WithdrawRequestController extends Controller
{

    /**
     * Withdraws list
     * @group Admin User > Wallets > Withdraw Requests
     */
    public function index()
    {
        try {
            return api()->success(null,WithdrawProfitResource::collection(WithdrawProfit::query()->simplePaginate())->response()->getData());
        } catch (\Throwable $exception) {
            Log::error('Admin/WithdrawRequestController@index => ' . serialize(request()->all()));
            return api()->error(null,[
                'subject' => $exception->getMessage()
            ]);
        }
    }

}
