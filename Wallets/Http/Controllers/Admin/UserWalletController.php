<?php

namespace Wallets\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Routing\Controller;
use Wallets\Http\Requests\Admin\GetUserWalletBalanceRequest;
use Wallets\Http\Requests\Admin\GetUserTransfersRequest;
use Wallets\Http\Requests\Admin\GetUserTransactionsRequest;
use Wallets\Http\Requests\Admin\UserDepositRequest;
use Wallets\Http\Requests\Admin\UserIdRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\WalletResource;
use Wallets\Models\WalletUser;
use Wallets\Services\BankService;

class UserWalletController extends Controller
{
    private $bankService;
    private $depositWallet;
    private $earningWallet;

    public function __construct(UserIdRequest $request)
    {
        $user = WalletUser::find($request->get('user_id'))->first();
        $this->bankService = new BankService($user);

        $this->earningWallet = config('earningWallet');
        $this->depositWallet = config('depositWallet');

        if($this->bankService->getAllWallets()->count() == 0) {
            $this->bankService->getWallet($this->depositWallet);
            $this->bankService->getWallet($this->earningWallet);
        }
    }

    /**
     * Get user's wallets list
     * @param UserIdRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletsList(UserIdRequest $request) //UserIdRequest needed for Scribe documentation
    {

        return api()->success(null,WalletResource::collection($this->bankService->getAllWallets()));

    }

    /**
     * Get user's wallet balance
     * @group Wallets > Admin
     * @param GetUserWalletBalanceRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletBalance(GetUserWalletBalanceRequest $request)
    {

        return api()->success(null,$this->bankService->getBalance($request->get('wallet_name')));

    }

    /**
     * Get user's wallet transactions
     * @group Wallets > Admin
     * @param GetUserTransactionsRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletTransactions(GetUserTransactionsRequest $request)
    {

        $data = $this->bankService->getTransactions($request->get('wallet_name'))->simplePaginate();
        return api()->success(null,TransactionResource::collection($data)->response()->getData());

    }

    /**
     * Get user's wallet transfers
     * @param GetUserTransfersRequest $request
     * @return JsonResponseAlias
     */
    public function getWalletTransfers(GetUserTransfersRequest $request)
    {
        $data = $this->bankService->getTransfers($request->get('wallet_name'))->simplePaginate();
        return api()->success(null,TransferResource::collection($data)->response()->getData());

    }

    /**
     * Deposit user's wallet
     * @group Wallets > Admin
     * @param UserDepositRequest $request
     * @return JsonResponseAlias
     */
    public function depositUser(UserDepositRequest $request)
    {
        $depositDescription = [
            'description' => $request->has('description') ? ' | ' . $request->get('description'): null,
            'is_admin' => true,
            'admin_id' => request()->header('X-user-id'),
        ];
        $transaction = $this->bankService->deposit(
            $request->get('wallet_name'),
            $request->get('amount'),
            $depositDescription);
        return api()->success(null,TransactionResource::make($transaction));

    }


}
