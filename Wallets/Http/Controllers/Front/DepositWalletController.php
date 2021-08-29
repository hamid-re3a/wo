<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\Front\ChargeDepositWalletRequest;
use Wallets\Http\Resources\TransactionHistoryResource;
use Wallets\Jobs\UrgentEmailJob;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Requests\Front\TransferFundFromDepositWallet;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\WalletResource;
use Wallets\Mail\DepositWallet\ReceiverFundEmail;
use Wallets\Mail\DepositWallet\SenderFundEmail;
use Wallets\Services\BankService;
use Wallets\Services\WalletService;

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
        return api()->success(null, WalletResource::make($this->bankService->getWallet($this->wallet)));

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
        $data = $this->bankService->getTransactions($this->wallet)
            ->simplePaginate();
        return api()->success(null, TransactionResource::collection($data)->response()->getData());

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
        return api()->success(null, TransferResource::collection($data)->response()->getData());

    }

    /**
     * Payment request
     * @group Public User > Deposit Wallet
     * @return JsonResponse
     */
    public function paymentRequest()
    {

    }

    /**
     * Transfer funds preview
     * @group Public User > Deposit Wallet
     * @param TransferFundFromDepositWallet $request
     * @return JsonResponse
     */
    public function transferPreview(TransferFundFromDepositWallet $request)
    {
        $this->prepareDepositWallet(); //Prepare logged in user wallet

        try {
            //Check logged in user balance for transfer
            $balance = $this->bankService->getBalance($this->wallet);
            if ($balance < $this->calculateNeededAmount($request->get('amount')))
                return api()->error(null, null, 406,trans('wallet.responses.not-enough-balance'));


            $to_user = User::query()->where('member_id', $request->get('member_id'))->get()->first();
            list($total, $fee) = $this->calculateTransferFee($request->get('amount'));

            $remain_balance = $balance - $total;


            return api()->success(null, [
                'receiver_member_id' => $to_user->member_id,
                'receiver_full_name' => $to_user->full_name,
                'received_amount' => walletPfAmount($request->get('amount')),
                'transfer_fee' =>  walletPfAmount($fee),
                'current_balance' =>  walletPfAmount($balance),
                'balance_after_transfer' =>  walletPfAmount($remain_balance)
            ]);

        } catch (\Throwable $exception) {
            Log::error('Transfer funds error .' . $exception->getMessage());
            return api()->error(trans('wallet.responses.something-went-wrong'));
        }
    }

    /**
     * Transfer Funds
     * @group Public User > Deposit Wallet
     * @param TransferFundFromDepositWallet $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function transferFunds(TransferFundFromDepositWallet $request)
    {
        $this->prepareDepositWallet(); //Prepare logged in user wallet

        try {
            DB::beginTransaction();
            //Check logged in user balance for transfer
            $balance = $this->bankService->getBalance($this->wallet);
            if ($balance < $this->calculateNeededAmount($request->get('amount')))
                return api()->error(null, null,406, trans('wallet.responses.not-enough-balance'));


            $to_user = User::query()->where('member_id', $request->get('member_id'))->get()->first();
            $receiver_bank_service = new BankService($to_user);
            list($amount, $fee) = $this->calculateTransferFee($request->get('amount'));


            $transfer = $this->bankService->transfer(
                $this->bankService->getWallet($this->wallet),
                $receiver_bank_service->getWallet($this->wallet),
                $request->get('amount'),
                [
                    'member_id' => $request->get('member_id'),
                    'fee' => $fee,
                    'type' => 'Transfer'
                ]
            );

            $this->bankService->withdraw($this->wallet, $fee,[
                'transfer_id' => $transfer->id
            ],'Transfer fee');

            UrgentEmailJob::dispatch(new SenderFundEmail(request()->wallet_user, $transfer), request()->wallet_user->email);
            UrgentEmailJob::dispatch(new ReceiverFundEmail($to_user, $transfer), $to_user->email);

            DB::commit();

            return api()->success(null, TransferResource::make($transfer));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Transfer funds error .' . $exception->getMessage());
            return api()->error(trans('wallet.responses.something-went-wrong'));
        }

    }

    /**
     * Deposit funds
     * @group Public User > Deposit Wallet
     * @param ChargeDepositWalletRequest $request
     * @return float|int|mixed
     */
    public function deposit(ChargeDepositWalletRequest $request)
    {
        $wallet_service = app(WalletService::class);
        $invoice = $wallet_service->invoiceWallet($request);
        return api()->success('success', [
            'payment_currency'=>$invoice->getPaymentCurrency(),
            'amount' => $invoice->getAmount(),
            'checkout_link' => $invoice->getCheckoutLink(),
            'transaction_id' => $invoice->getTransactionId(),
            'expiration_time' => $invoice->getExpirationTime(),
        ]);
    }

    private function calculateNeededAmount($amount)
    {
        $percentage_fee = walletGetSetting('percentage_transfer_fee');
        $fix_fee = walletGetSetting('fix_transfer_fee');
        if (!empty($percentage_fee) AND $percentage_fee > 0)
            return $amount + ($amount * $percentage_fee / 100);

        return $amount + $fix_fee;
    }

    private function calculateTransferFee($amount)
    {
        $percentage_fee = walletGetSetting('percentage_transfer_fee');
        $fix_fee = walletGetSetting('fix_transfer_fee');
        $transaction_fee_way = walletGetSetting('transaction_fee_calculation');

        if (!empty($transaction_fee_way) AND $transaction_fee_way == 'percentage' AND !empty($percentage_fee) AND $percentage_fee > 0)
            $fix_fee = $amount * $percentage_fee / 100;

        if (empty($fix_fee) OR $fix_fee <= 0)
            $fix_fee = 10;
        $total = $amount + $fix_fee;
        return [$total, $fix_fee];
    }
}
