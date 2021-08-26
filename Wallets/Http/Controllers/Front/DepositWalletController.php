<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use User\Models\User;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Requests\Front\TransferFundFromDepositWallet;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\WalletResource;
use Wallets\Services\BankService;

class DepositWalletController extends Controller
{
    private $bankService;
    private $wallet;

    private function prepareDepositWallet()
    {
        $this->bankService = new BankService(request()->wallet_user);
        $this->wallet = config('depositWallet');
    }

    /**
     * Get Deposit wallet
     * @group Public User > Deposit Wallet
     */
    public function index()
    {
        $this->prepareDepositWallet();
        return api()->success(null,WalletResource::make($this->bankService->getWallet($this->wallet)));

    }

    /**
     * Get transactions
     * @group Public User > Deposit Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {

        $this->prepareDepositWallet();
        $data = $this->bankService->getTransactions($this->wallet)->simplePaginate();
        return api()->success(null,TransactionResource::collection($data)->response()->getData());

    }

    /**
     * Get transfers
     * @group Public User > Deposit Wallet
     * @return JsonResponse
     */
    public function transfers()
    {

        $this->prepareDepositWallet();
        $data = $this->bankService->getTransfers($this->wallet)->simplePaginate();
        return api()->success(null,TransferResource::collection($data)->response()->getData());

    }

    /**
     * Transfer Funds
     * @group Public User > Deposit Wallet
     * @param TransferFundFromDepositWallet $request
     * @return JsonResponse
     */
    public function transfer(TransferFundFromDepositWallet $request)
    {
        $this->prepareDepositWallet(); //Prepare logged in user wallet

        try {
            DB::beginTransaction();
            //Check logged in user balance for transfer
            if($this->bankService->getBalance($this->wallet) < $this->calculateNeededAmount($request->get('amount')))
                return api()->error(trans('wallet.responses.not-enough-balance'), null,406);


            $to_user = User::query()->where('member_id', $request->get('member_id'))->get()->first();
            $receiver_bank_service = new BankService($to_user);
            $fee = $this->calculateTransferFee($request->get('amount'));


            $transfer = $this->bankService->transfer(
                $this->bankService->getWallet($this->wallet),
                $receiver_bank_service->getWallet($this->wallet),
                $request->get('amount'),
                [
                    'member_id' => $request->get('member_id'),
                    'fee' => $fee
                ]
            );

            $this->bankService->withdraw($this->wallet,$fee,[
                'type' => 'Transfer fee'
            ]);

            DB::commit();

            return api()->success(null,TransferResource::make($transfer));
        } catch (\Throwable $exception) {
            DB::rollBack();
        }

    }

    private function calculateNeededAmount($amount)
    {
        $percentage_fee = walletGetSetting('percentage_transfer_fee');
        $fix_fee = walletGetSetting('fix_transfer_fee');
        if(!empty($percentage_fee) AND $percentage_fee > 0)
            return $amount + ($amount*$percentage_fee / 100);

        return $amount + $fix_fee;
    }

    private function calculateTransferFee($amount)
    {
        $percentage_fee = walletGetSetting('percentage_transfer_fee');
        $transaction_fee = walletGetSetting('fix_transfer_fee');
        if(!empty($percentage_fee) AND $percentage_fee > 0)
            $transaction_fee = $amount * $percentage_fee / 100;

        return $transaction_fee;
    }
}
