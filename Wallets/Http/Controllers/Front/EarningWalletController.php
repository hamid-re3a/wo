<?php

namespace Wallets\Http\Controllers\Front;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\ChartTypeRequest;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Requests\Front\TransferFundFromEarningWalletRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\EarningWalletResource;
use Wallets\Repositories\WalletRepository;
use Wallets\Services\BankService;

class EarningWalletController extends Controller
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
            $this->prepareEarningWallet();
        $this->wallet_repository = $wallet_repository;
    }
    private function prepareEarningWallet()
    {
        /**@var $user User*/
        $user = auth()->user();

        $this->bankService = new BankService($user);
        $this->walletName = WALLET_NAME_EARNING_WALLET;
        $this->walletObject = $this->bankService->getWallet($this->walletName);

    }

    /**
     * Get earned commissions
     * @group Public User > Earning Wallet
     */
    public function earned_commissions()
    {
        $commissions = [
            'Binary',
            'Direct Sale',
            'Indirect Sale',
            'Trading Profit',
            'Residual Bonus',
            'Trainer Bonus',
        ];

        return api()->success(null, $this->wallet_repository->getTransactionsSumByTypes(auth()->user->id,$commissions));

    }

    /**
     * Get wallet
     * @group Public User > Earning Wallet
     */
    public function index()
    {

        return api()->success(null, EarningWalletResource::make($this->walletObject));

    }

    /**
     * Get transactions
     * @group Public User > Earning Wallet
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
            ]
        ]);

    }

    /**
     * Get transfers
     * @group Public User > Earning Wallet
     * @return JsonResponse
     */
    public function transfers()
    {

        $list = $this->bankService->getTransfers($this->walletName)->paginate();
        return api()->success(null, [
            'list' => TransferResource::collection($list),
            'pagination' => [
                'total' => $list->total(),
                'per_page' => $list->perPage(),
            ]
        ]);

    }

    /**
     * Transfer funds preview
     * @group Public User > Earning Wallet
     * @param TransferFundFromEarningWalletRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function transfer_to_deposit_wallet_preview(TransferFundFromEarningWalletRequest $request)
    {

        try {
            $to_user = null;
            $amount = $request->get('amount');
            $fee = 0;

            if ($request->has('member_id')) {
                $to_user = User::query()->where('member_id', '=', $request->get('member_id'))->first();
                list($amount, $fee) = $this->calculateTransferAmount($request->get('amount'));
            }
            $balance = $this->bankService->getBalance($this->walletName);
            $remain_balance = $balance - $amount;

            return api()->success(null, [
                'receiver_member_id' => $to_user ? $to_user->member_id : null,
                'receiver_full_name' => $to_user ? $to_user->full_name : null,
                'received_amount' => $request->get('amount'),
                'transfer_fee' => $fee,
                'current_balance' => $balance,
                'balance_after_transfer' => $remain_balance
            ]);

        } catch (\Throwable $exception) {
            Log::error('Transfer funds error :' . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * Transfer funds
     * @group Public User > Earning Wallet
     * @param TransferFundFromEarningWalletRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function transfer_to_deposit_wallet(TransferFundFromEarningWalletRequest $request)
    {

        try {
            DB::beginTransaction();

            $deposit_wallet = $this->bankService->getWallet(WALLET_NAME_DEPOSIT_WALLET);
            $amount = request()->get('amount');
            $description = [];
            $fee = 0;
            if ($request->has('member_id')) {
                $other_member = User::query()->where('member_id', '=', $request->get('member_id'))->first();
                $other_member_bank_service = new BankService($other_member);
                $deposit_wallet = $other_member_bank_service->getWallet(WALLET_NAME_DEPOSIT_WALLET);
                list($total, $fee) = $this->calculateTransferAmount($amount);
                $description = [
                    'member_id' => $request->get('member_id'),
                    'fee' => $fee,
                    'type' => 'Transfer'
                ];

                $balance = $this->bankService->getBalance(WALLET_NAME_EARNING_WALLET);
                if ($balance < $total)
                    return api()->error(null, null, 406, [
                        'subject' => trans('wallet.responses.not-enough-balance', [
                            'amount' => $amount,
                            'fee' => $fee
                        ])
                    ]);

            }

            $transfer = $this->bankService->transfer(
                $this->bankService->getWallet($this->walletName),
                $deposit_wallet,
                $amount,
                $description
            );

            if ($request->has('member_id') AND $fee > 0)
                $this->bankService->withdraw($this->walletName, $fee, [
                    'transfer_id' => $transfer->id
                ], 'Transfer fee');

            DB::commit();

            return api()->success(null, TransferResource::make($transfer));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Transfer funds error .' . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * Overall balance chart
     * @group Public User > Earning Wallet
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function overallBalanceChart(ChartTypeRequest $request)
    {
        return api()->success(null, $this->wallet_repository->getWalletOverallBalanceChart($request->get('type'),$this->walletObject->id));
    }

    /**
     * Commissions chart
     * @group Public User > Earning Wallet
     * @param ChartTypeRequest $request
     * @return JsonResponse
     */
    public function commissionsChart(ChartTypeRequest $request)
    {
        $commissions = [
            'Binary',
            'Direct Sale',
            'Indirect Sale',
            'Trading Profit',
            'Residual Bonus',
            'Trainer Bonus',
        ];

        return api()->success(null,$this->wallet_repository->getCommissionsChart($request->get('type'),$commissions,$this->walletObject->id));
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
