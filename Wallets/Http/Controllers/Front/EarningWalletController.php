<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use Wallets\Http\Requests\Front\CreateWithdrawRequest;
use Wallets\Http\Requests\Front\TransactionRequest;
use Wallets\Http\Requests\Front\TransferFundFromEarningWalletRequest;
use Wallets\Http\Resources\TransactionResource;
use Wallets\Http\Resources\TransferResource;
use Wallets\Http\Resources\EarningWalletResource;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Models\Transaction;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;

class EarningWalletController extends Controller
{
    /**
     * @var $bankService BankService
     * @var $user User
     */
    private $bankService;
    private $wallet;

    private function prepareEarningWallet()
    {
        $this->bankService = new BankService(auth()->user());
        $this->wallet = config('earningWallet');
        $this->bankService->getWallet($this->wallet);

    }

    /**
     * Get earned commissions
     * @group Public User > Earning Wallet
     */
    public function earned_commissions()
    {
        $this->prepareEarningWallet();
        $counts = User::query()->whereId(auth()->user()->id)
            ->withSumQuery(['transactions.amount AS binary_commissions_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'Binary Commissions');
                    });
                }]
            )
            ->withSumQuery(['transactions.amount AS direct_commissions_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'Direct Commissions');
                    });
                }]
            )
            ->withSumQuery(['transactions.amount AS indirect_commissions_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'Indirect Commissions');
                    });
                }]
            )
            ->withSumQuery(['transactions.amount AS roi_sum' => function (Builder $query) {
                    $query->where('type', '=', 'deposit');
                    $query->whereHas('metaData', function (Builder $subQuery) {
                        $subQuery->where('name', '=', 'ROI');
                    });
                }]
            )
            ->withSumQuery(['transactions.amount AS spent_sum' => function (Builder $query) {
                    $query->where('type', '=', 'withdraw');
                }]
            )
            ->first();

        return api()->success(null, [
            'binary_commissions_sum' => $counts->binary_commissions_sum ? $counts->binary_commissions_sum : 0,
            'direct_commissions_sum' => $counts->direct_commissions_sum ? $counts->direct_commissions_sum : 0,
            'indirect_commissions_sum' => $counts->indirect_commissions_sum ? $counts->indirect_commissions_sum : 0,
            'roi_sum' => $counts->roi_sum ? $counts->roi_sum : 0,
            'spent_sum' => $counts->spent_sum ? $counts->spent_sum : 0,
        ]);

    }

    /**
     * Get wallet
     * @group Public User > Earning Wallet
     */
    public function index()
    {

        $this->prepareEarningWallet();
        return api()->success(null, EarningWalletResource::make($this->bankService->getWallet($this->wallet)));

    }

    /**
     * Get transactions
     * @group Public User > Earning Wallet
     * @param TransactionRequest $request
     * @return JsonResponse
     */
    public function transactions(TransactionRequest $request)
    {

        $this->prepareEarningWallet();
        $list = $this->bankService->getTransactions($this->wallet)->paginate();
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

        $this->prepareEarningWallet();
        $list = $this->bankService->getTransfers($this->wallet)->paginate();
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

        $this->prepareEarningWallet();
        try {
            $to_user = null;
            $amount = $request->get('amount');
            $fee = 0;

            if ($request->has('member_id')) {
                $to_user = User::query()->where('member_id', '=', $request->get('member_id'))->first();
                list($amount, $fee) = $this->calculateTransferAmount($request->get('amount'));
            }
            $balance = $this->bankService->getBalance(config('earningWallet'));
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
        $this->prepareEarningWallet();

        try {
            DB::beginTransaction();

            $deposit_wallet = $this->bankService->getWallet(config('depositWallet'));
            $amount = request()->get('amount');
            $description = [];
            $fee = 0;
            if ($request->has('member_id')) {
                $other_member = User::query()->where('member_id', '=', $request->get('member_id'))->first();
                $other_member_bank_service = new BankService($other_member);
                $deposit_wallet = $other_member_bank_service->getWallet(config('depositWallet'));
                list($total, $fee) = $this->calculateTransferAmount($amount);
                $description = [
                    'member_id' => $request->get('member_id'),
                    'fee' => $fee,
                    'type' => 'Transfer'
                ];

                $balance = $this->bankService->getBalance(config('earningWallet'));
                if ($balance < $total)
                    return api()->error(null, null, 406, [
                        'subject' => trans('wallet.responses.not-enough-balance', [
                            'amount' => $amount,
                            'fee' => $fee
                        ])
                    ]);

            }

            $transfer = $this->bankService->transfer(
                $this->bankService->getWallet($this->wallet),
                $deposit_wallet,
                $amount,
                $description
            );

            if ($request->has('member_id') AND $fee > 0)
                $this->bankService->withdraw($this->wallet, $fee, [
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

    private function calculateTransferAmount($amount)
    {
        $transfer_fee = walletGetSetting('transfer_fee');
        $transaction_fee_way = walletGetSetting('transaction_fee_calculation');

        if (!empty($transaction_fee_way) AND $transaction_fee_way == 'percentage' AND !empty($transfer_fee) AND $transfer_fee > 0)
            $transfer_fee = $amount * $transfer_fee / 100;

        if (empty($transfer_fee) OR $transfer_fee <= 0)
            $transfer_fee = 10;

        $total = $amount + $transfer_fee;
        return [$total, $transfer_fee];
    }
}
