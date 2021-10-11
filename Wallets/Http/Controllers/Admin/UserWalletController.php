<?php

namespace Wallets\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use User\Models\User;
use Wallets\Http\Requests\Admin\GetUserWalletBalanceRequest;
use Wallets\Http\Requests\Admin\GetUserTransfersRequest;
use Wallets\Http\Requests\Admin\GetUserTransactionsRequest;
use Wallets\Http\Requests\Admin\UserIdRequest;
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
    private $depositWallet;
    private $earningWallet;

    public function __construct()
    {
        if (request()->has('user_id'))
            $this->prepare(request());
    }

    private function prepare(Request $request)
    {

        $user = User::query()->whereMemberId($request->get('user_id'))->first();
        $this->bankService = new BankService($user);

        $this->earningWallet = config('earningWallet');
        $this->depositWallet = config('depositWallet');

        if ($this->bankService->getAllWallets()->count() == 0) {
            $this->bankService->getWallet($this->depositWallet);
            $this->bankService->getWallet($this->earningWallet);
        }

    }

    /**
     * Get all transactions
     * @group Admin User > Wallets
     */
    public function getAllTransactions()
    {
        //Get all transactions except admin's wallet
        $list = Transaction::query()->where('payable_id', '!=', 1)->paginate();
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
        $list = $this->bankService->getTransactions($request->get('wallet_name'))->paginate();
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
        $list = $this->bankService->getTransfers($request->get('wallet_name'))->paginate();
        return api()->success(null, [
            'list' => TransferResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);

    }

}
