<?php

namespace Wallets\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use User\Models\User;
use Wallets\Http\Requests\Admin\GetUserWalletBalanceRequest;
use Wallets\Http\Requests\Admin\GetUserTransfersRequest;
use Wallets\Http\Requests\Admin\GetUserTransactionsRequest;
use Wallets\Http\Requests\Admin\UserIdRequest;
use Wallets\Http\Requests\Admin\GetTransactionsRequest;
use Wallets\Http\Resources\Admin\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\EarningWalletResource;
use Wallets\Models\Transaction;
use Wallets\Services\BankService;

class UserWalletController extends Controller
{

    /**
     * @var $bankService BankService
     */

    private $bankService;

    public function __construct()
    {
        if (request()->has('member_id'))
            $this->prepare();
    }

    private function prepare()
    {

        $user = User::query()->whereMemberId(request()->get('member_id'))->first();
        $this->bankService = new BankService($user);

        if ($this->bankService->getAllWallets()->count() != count(WALLET_NAMES))
            foreach(WALLET_NAMES AS $wallet_name)
                $this->bankService->getWallet($wallet_name);

    }

    /**
     * Get all transactions
     * @group Admin User > Wallets
     * @param GetTransactionsRequest $request
     * @return JsonResponseAlias
     */
    public function getAllTransactions(GetTransactionsRequest $request)
    {
        //Get all transactions except admin's wallet
        $list = Transaction::query()->with([
            'payable',
            'wallet'
        ]);
        if ($request->has('wallet_name'))
            $list = $list->whereHas('wallet', function (Builder $query) use ($request) {
                $query->where('name', '=', $request->get('wallet_name'));
                $query->orWhere('slug', '=', Str::slug($request->get('wallet_name')));
            });

        $list = $list->where('payable_id', '!=', 1)->orderByDesc('id')->paginate();
        return api()->success(null, [
            'list' => TransactionResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);

    }

    /**
     * Get user's wallets
     * @group Admin User > Wallets
     * @param UserIdRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletsList(UserIdRequest $request) //UserIdRequest needed for Scribe documentation
    {
        return api()->success(null, EarningWalletResource::collection($this->bankService->getAllWallets()));

    }

    /**
     * Get user's wallet balance
     * @group Admin User > Wallets
     * @param GetUserWalletBalanceRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletBalance(GetUserWalletBalanceRequest $request)
    {
        return api()->success(null, [
            'balance' => $this->bankService->getBalance($request->get('wallet_name'))
        ]);

    }

    /**
     * Get user's wallet transactions
     * @group Admin User > Wallets
     * @param GetUserTransactionsRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletTransactions(GetUserTransactionsRequest $request)
    {
        $list = $this->bankService->getTransactions($request->get('wallet_name'))->orderByDesc('id')->paginate();
        return api()->success(null, [
            'list' => TransactionResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);

    }

    /**
     * Get user's wallet transfers
     * @group Admin User > Wallets
     * @param GetUserTransfersRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletTransfers(GetUserTransfersRequest $request)
    {
        $list = $this->bankService->getTransfers($request->get('wallet_name'))->orderByDesc('id')->paginate();
        return api()->success(null, [
            'list' => TransferResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);

    }

}
