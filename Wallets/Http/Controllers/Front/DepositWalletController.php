<?php

namespace Wallets\Http\Controllers\Front;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\ChartTypeRequest;
use Wallets\Http\Requests\Front\AskFundRequest;
use Wallets\Http\Requests\Front\ChargeDepositWalletRequest;
use Wallets\Http\Resources\DepositWalletResource;
use Wallets\Jobs\UrgentEmailJob;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Requests\Front\TransferFundFromDepositWallet;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Mail\DepositWallet\ReceiverFundEmail;
use Wallets\Mail\DepositWallet\RequestFundEmail;
use Wallets\Mail\DepositWallet\SenderFundEmail;
use Wallets\Repositories\WalletRepository;
use Wallets\Services\BankService;
use Wallets\Services\WalletService;

class DepositWalletController extends Controller
{
    /**@var $bankService BankService*/
    private $bankService;
    private $walletName;
    /**@var $walletObject Wallet*/
    private $walletObject;
    /**@var $user User*/
    private $user;
    private $wallet_repository;

    public function __construct(WalletRepository $wallet_repository)
    {
        if(auth()->check())
            $this->prepareDepositWallet();
        $this->wallet_repository = $wallet_repository;
    }

    private function prepareDepositWallet()
    {
        $this->user = auth()->user();

        $this->bankService = new BankService($this->user);
        $this->walletName = WALLET_NAME_DEPOSIT_WALLET;
        $this->walletObject = $this->bankService->getWallet($this->walletName);
    }

    /**
     * Get wallet
     * @group Public User > Deposit Wallet
     */
    public function index()
    {
        return api()->success(null, DepositWalletResource::make($this->bankService->getWallet($this->walletName)));

    }

    /**
     * Get transactions
     * @group Public User > Deposit Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {

        $list = $this->bankService->getTransactions($this->walletName)->paginate();
        return api()->success(null, [
            'list' => TransactionResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]]);

    }

    /**
     * Get transfers
     * @group Public User > Deposit Wallet
     * @return JsonResponse
     */
    public function transfers()
    {

        $data = $this->bankService->getTransfers($this->walletName)->simplePaginate();
        return api()->success(null, TransferResource::collection($data)->response()->getData());

    }

    /**
     * Payment request
     * @group Public User > Deposit Wallet
     * @param AskFundRequest $request
     * @return JsonResponse
     */
    public function paymentRequest(AskFundRequest $request)
    {
        $user = User::query()->where('member_id', $request->get('member_id'))->first();
        UrgentEmailJob::dispatch(new RequestFundEmail($user, $this->user, $request->get('amount')), $user->email);

        return api()->success(null, [
            'amount' => $request->get('amount'),
            'receiver_full_name' => $user->full_name
        ]);

    }

    /**
     * Transfer funds preview
     * @group Public User > Deposit Wallet
     * @param TransferFundFromDepositWallet $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function transferPreview(TransferFundFromDepositWallet $request)
    {

        try {
            //Check logged in user balance for transfer
            $balance = $this->bankService->getBalance($this->walletName);
            list($amount, $fee) = $this->calculateTransferAmount($request->get('amount'));

            if ($balance < $amount)
                return api()->error(null, null, 406, [
                    'subject' => trans('wallet.responses.not-enough-balance')
                ]);


            $to_user = User::query()->where('member_id', $request->get('member_id'))->get()->first();
            list($total, $fee) = $this->calculateTransferAmount($request->get('amount'));

            $remain_balance = $balance - $total;


            return api()->success(null, [
                'receiver_member_id' => $to_user->member_id,
                'receiver_full_name' => $to_user->full_name,
                'received_amount' => $request->get('amount'),
                'transfer_fee' => $fee,
                'current_balance' => $balance,
                'balance_after_transfer' => $remain_balance
            ]);

        } catch (\Throwable $exception) {
            Log::error('Transfer funds error .' . $exception->getMessage());

            throw $exception;
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

        try {
            DB::beginTransaction();
            //Check logged in user balance for transfer
            $balance = $this->bankService->getBalance($this->walletName);
            list($amount, $fee) = $this->calculateTransferAmount($request->get('amount'));

            if ($balance < $amount)
                return api()->error(null, null, 406, [
                    'subject' => trans('wallet.responses.not-enough-balance')
                ]);


            $to_user = User::query()->where('member_id', $request->get('member_id'))->get()->first();
            $receiver_bank_service = new BankService($to_user);


            $transfer = $this->bankService->transfer(
                $this->bankService->getWallet($this->walletName),
                $receiver_bank_service->getWallet($this->walletName),
                $request->get('amount'),
                [
                    'member_id' => $request->get('member_id'),
                    'fee' => $fee,
                    'type' => 'Funds transferred'
                ]
            );

            $this->bankService->withdraw($this->walletName, $fee, [
                'transfer_id' => $transfer->id
            ], 'Transfer fee');

            UrgentEmailJob::dispatch(new SenderFundEmail(auth()->user(), $transfer), auth()->user()->email);
            UrgentEmailJob::dispatch(new ReceiverFundEmail($to_user, $transfer), $to_user->email);

            DB::commit();

            return api()->success(null, TransferResource::make($transfer));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Transfer funds error .' . $exception->getMessage());
            throw $exception;
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
        list($flag, $response) = $wallet_service->invoiceWallet($request);

        if (!$flag)
            return api()->error(null, [
                'subject' => $response
            ]);

        return api()->success('success', [
            'payment_currency' => $response['payment_currency'],
            'amount' => $response['amount'],
            'checkout_link' => $response['checkout_link'],
            'transaction_id' => $response['transaction_id'],
            'expiration_time' => $response['expiration_time'],
        ]);
    }

    /**
     * Overall balance chart
     * @group Public User > Deposit Wallet
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function overallBalanceChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->wallet_repository->getWalletOverallBalance($request->get('type'),$this->walletObject->id));
    }

    /**
     * Investments chart
     * @group Public User > Deposit Wallet
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function investmentsChart(ChartTypeRequest $request)
    {
        return api()->success(null,$this->wallet_repository->getWalletInvestmentChart($request->get('type'), $this->walletObject->id));
    }

    private function calculateTransferAmount($amount)
    {
        $transfer_fee = getWalletSetting('transfer_fee');
        $transaction_fee_way = getWalletSetting('transaction_fee_calculation');

        if (!empty($transaction_fee_way) AND $transaction_fee_way == 'percentage' AND !empty($transfer_fee) AND $transfer_fee > 0)
            $transfer_fee = $amount * $transfer_fee / 100;

        if (empty($transfer_fee) OR $transfer_fee <= 0)
            $transfer_fee = 10;

        $total = $amount + $transfer_fee;
        return [$total, $transfer_fee];
    }
}
