<?php


namespace Wallets\Repositories;


use Giftcode\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use Payments\Services\Processors\PayoutProcessor;
use User\Models\User;
use User\Services\Grpc\WalletInfo;
use User\Services\Grpc\WalletType;
use Wallets\Models\Transaction;
use Wallets\Models\WithdrawProfit;
use Wallets\Models\WithdrawProfitHistory;
use Wallets\Services\BankService;

class WithdrawRepository
{
    /**
     * @var $bankService BankService
     *
     */
    private $bankService;
    private $payout_processor;
    private $user_wallet;
    private $model;
    private $model_history;

    public function __construct(PayoutProcessor $payout_processor)
    {
        $this->payout_processor = $payout_processor;
        $this->user_wallet = WALLET_NAME_EARNING_WALLET;
        $this->model = new WithdrawProfit();
        $this->model_history = new WithdrawProfitHistory();
    }

    public function makeWithdrawRequest(array $request): WithdrawProfit
    {
        try {
            if (!getWalletSetting('withdrawal_request_is_enabled'))
                throw new \Exception(trans('wallet.withdraw-profit-request.withdrawal-requests-is-not-active'));

            /**@var $user User */
            $user = array_key_exists('user_id', $request) ? User::query()->find($request['user_id']) : auth()->user();
            $request['user_id'] = $user->id;
            $this->bankService = new BankService($user);

            switch ($request['currency']) {
                case 'BTC' :
                    return $this->withdrawBTC($request);
                    break;
                default:
                    throw new \Exception(trans('wallet.responses.something-went-wrong'), 406);
                    break;
            }
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function withdrawBTC(array $request): WithdrawProfit
    {
        try {
            $btc_price = Http::get('https://blockchain.info/ticker');

            if ($btc_price->ok() AND is_array($btc_price->json()) AND isset($btc_price->json()['USD']['15m'])) {

                list($total, $fee) = calculateWithdrawalFee($request['amount'], $request['currency']);

                //Check user balance again
                if ($this->bankService->getBalance($this->user_wallet) < $total)
                    throw new \Exception(trans('wallet.responses.not-enough-balance'),406);

                $withdraw_transaction = $this->bankService->withdraw($this->user_wallet, $request['amount'], null, 'Withdrawal request', null, true, false);
                $withdraw_fee = $this->bankService->withdraw($this->user_wallet, $fee, null, 'Withdrawal request fee', null);

                list($flag, $response) = $this->getUserBTCWalletHash($request['user_id']);
                if (!$flag) {
                    Log::error('Wallets\Repositories\WithdrawRepository@withdrawBTC =>' . $response);
                    throw new \Exception($response,406);
                }

                return WithdrawProfit::create([
                    'user_id' => $request['user_id'],
                    'withdraw_transaction_id' => $withdraw_transaction->id,
                    'wallet_hash' => $response,
                    'payout_service' => 'btc-pay-server', //TODO improvements or like payment drivers ?!
                    'currency' => $request['currency'],
                    'pf_amount' => $request['amount'],
                    'fee' => (double)$fee,
                    'crypto_amount' => pfToUsd((double)$request['amount']) / $btc_price->json()['USD']['15m'],
                    'crypto_rate' => $btc_price->json()['USD']['15m']
                ]);

            } else {

                throw new \Exception(trans('wallet.withdraw-profit-request.external-resource-error', [
                    'server' => 'BlockChain.info'
                ]), 500);

            }
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function update(array $validated_request, WithdrawProfit $withdrawProfit)
    {
        switch ($validated_request['status']) {
            case WALLET_WITHDRAW_COMMAND_PROCESS :
                $this->pay($withdrawProfit->toArray());
                break;
            case WALLET_WITHDRAW_COMMAND_REJECT :
                $this->rejectWithdrawRequest($validated_request, $withdrawProfit);
                break;
            case WALLET_WITHDRAW_COMMAND_REVERT :
                //Check if request is marked as rejected
                if($withdrawProfit->getRawOriginal('status') != WALLET_WITHDRAW_COMMAND_REJECT)
                    throw new Exception(trans('wallet.withdraw-profit-request.you-can-revert-a-rejected-request'),406);

                //Make a fresh withdraw request
                $this->makeWithdrawRequest([
                    'amount' => $withdrawProfit->pf_amount,
                    'currency' => $withdrawProfit->currency,
                    'user_id' => $withdrawProfit->user_id
                ]);

                $withdrawProfit->update([
                    'status' => WALLET_WITHDRAW_COMMAND_REVERT
                ]);
                break;
            case WALLET_WITHDRAW_COMMAND_POSTPONE:
            default:
                $withdrawProfit->update($validated_request);
                break;
        }
    }

    public function pay($withdrawProfits, $dispatchType = 'dispatchSync')
    {
        try {
            if (is_array($withdrawProfits) AND array_key_exists('currency', $withdrawProfits)) {
                $currency = $withdrawProfits['currency'];
                $amount = $withdrawProfits['crypto_amount'];
                $withdrawProfits = collect()->push($withdrawProfits);
            } else if ($withdrawProfits instanceof Collection)
                list($currency, $amount) = $this->checkWithdrawRequestsForPayOut($withdrawProfits);
            else
                throw new \Exception(trans('wallet.responses.something-went-wrong'));

            $this->checkBPSWalletBalance($amount);
            $this->payout_processor->pay($currency, $withdrawProfits, $dispatchType);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function payoutGroup(array $ids)
    {
        try {
            DB::beginTransaction();
            $withdrawals_requests = $this->model->query()->where('status', '=', 1)->whereIn('uuid', $ids)->get();
            $this->pay($withdrawals_requests);
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function rejectWithdrawRequest(array $request, WithdrawProfit $withdraw_request)
    {
        $admin_bank_service = new BankService(User::query()->find(1));
        $admin_balance = $admin_bank_service->getBalance(WALLET_NAME_DEPOSIT_WALLET);
        if($admin_balance < $withdraw_request->fee) {
            //TODO Notify admin
            Log::error('Wallets\Repositories\WithdrawRepository@rejectWithdrawRequest => Insufficient PF in admin Deposit Wallet');
            Log::error('Admin Balance => ' . $admin_balance . ' | Fee => ' . $withdraw_request->fee . ' | WithdrawalID => ' . $withdraw_request->id);
            throw new \Exception(trans('wallet.responses.something-went-wrong'),500);
        }

        try {
            //Charge Admin deposit wallet for withdrawal fee
            $admin_bank_service->withdraw(WALLET_NAME_DEPOSIT_WALLET, $withdraw_request->fee, [
                'description' => 'Refund withdrawal request fee',
                'withdrawal_request_id' => $withdraw_request->id
            ], 'Withdrawal refund', null);

            //Deposit user wallet
            $user_bank_service = new BankService($withdraw_request->user);

            //Refund Withdrawal amount
            $refund_transaction = $user_bank_service->deposit($this->user_wallet, $withdraw_request->getTotalAmount(), [
                'description' => 'Withdrawal request rejected',
                'withdrawal_request_id' => $withdraw_request->id,
            ], true, 'Withdrawal refund');

//            //Refund Withdrawal FEE amount
//            $refund_fee_transaction = $bank_service->deposit($this->user_wallet, $withdraw_request->fee, [
//                'description' => 'Withdrawal request rejected',
//                'withdrawal_request_id' => $withdraw_request->id,
//            ], true, 'Withdrawal fee refund');

            $withdraw_request->update([
                'status' => $request['status'],
                'refund_transaction_id' => $refund_transaction instanceof Transaction ? $refund_transaction->id : $withdraw_request->refund_transaction_id
            ]);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function revertWithdrawRequest(WithdrawProfit $withdrawProfit)
    {

    }

    public function checkWithdrawRequestsForPayOut(Collection $withdraw_requests): array
    {
        try {
            /**
             * @var $first_request WithdrawProfit
             */
            $first_request = $withdraw_requests->first();
            if ($withdraw_requests->where('currency', '!=', $first_request->currency)->count())
                throw new \Exception(trans('wallet.withdraw-profit-request.different-currency-payout'));

            return [$first_request->currency, $withdraw_requests->sum('crypto_amount')];
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function getPendingAmountVsTimeChart($type, $user_id = null)
    {
        try {
            $that = $this;
            $function_withdraw_requests = function ($from_date, $to_date) use ($that, $user_id) {
                return $that->getWithdrawRequestsByDateCollection('created_at', $from_date, $to_date, null, $user_id);
            };

            $sub_function = function ($collection, $intervals) {
                /**@var $collection Collection */
                return $collection->whereBetween('created_at', $intervals)->sum(function ($withdraw_request) {
                    /**@var $withdraw_request WithdrawProfit */
                    return $withdraw_request->pf_amount;
                });
            };

            $result = [];
            $result['withdraw_requests'] = chartMaker($type, $function_withdraw_requests, $sub_function);
            return $result;

        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\WithdrawRepository@getPendingAmountVsTimeChart => ' . $exception->getMessage());
            throw new \Exception(trans('wallet.responses.something-went-wrong'));
        }
    }

    public function getPaidAmountVsTimeChart($type, $user_id = null)
    {
        try {
            $that = $this;
            $function_withdraw_requests = function ($from_date, $to_date) use ($that, $user_id) {
                return $that->getWithdrawRequestsByDateCollection('updated_at', $from_date, $to_date, WALLET_WITHDRAW_COMMAND_PROCESS, $user_id);
            };

            $sub_function = function ($collection, $intervals) {
                /**@var $collection Collection */
                return $collection->whereBetween('created_at', $intervals)->sum(function ($withdraw_request) {
                    /**@var $withdraw_request WithdrawProfit */
                    return $withdraw_request->pf_amount;
                });
            };

            $result = [];
            $result['withdraw_requests'] = chartMaker($type, $function_withdraw_requests, $sub_function);
            return $result;

        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\WithdrawRepository@getPendingAmountVsTimeChart => ' . $exception->getMessage());
            throw new \Exception(trans('wallet.responses.something-went-wrong'));
        }
    }

    public function checkBPSWalletBalance($amount)
    {
        try {
            $wallet_response = Http::withHeaders(['Authorization' => config('payment.btc-pay-server-api-token')])
                ->get(
                    config('payment.btc-pay-server-domain') . 'api/v1/stores/' .
                    config('payment.btc-pay-server-store-id') . '/payment-methods/OnChain/BTC/wallet/');


            if ($wallet_response->ok() AND is_array($wallet_response->json()) AND isset($wallet_response->json()['confirmedBalance'])) {

                $wallet_balance = $wallet_response->json()['confirmedBalance'];

                if ((float)$amount > (float)$wallet_balance)
                    throw new \Exception(trans('wallet.withdraw-profit-request.insufficient-bpb-wallet-balance', [
                        'amount' => ($amount - $wallet_balance)
                    ]));

            } else {
                Log::error('BPS Wallet balance => ' . $wallet_response->json()['confirmedBalance']);
                throw new \Exception(trans('wallet.withdraw-profit-request.check-bps-or-blockchain.com-server', [
                    'server' => 'BTCPayServer'
                ]));
            }
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    private function getUserBTCWalletHash(int $user_id): array
    {
        try {
            if (app()->environment('testing'))
                return [true, 'grpc called'];

            $client = new \User\Services\Grpc\UserServiceClient(env('API_GATEWAY_GRPC_URL', 'staging-api-gateway.janex.org:9595'), [
                'credentials' => \Grpc\ChannelCredentials::createInsecure()
            ]);
            $req = app(\User\Services\Grpc\WalletRequest::class);
            $req->setUserId((int)$user_id);
            $req->setWalletType(\User\Services\Grpc\WalletType::BTC);
            /**@var $reply WalletInfo */
            list($reply, $status) = $client->getUserWalletInfo($req)->wait();
            if (!$status OR $status->code != 0 OR !$reply->getAddress())
                return [false, trans('wallet.withdraw-profit-request.cant-find-wallet-address', [
                    'name' => 'bitcoin'
//                'name' => WalletType::name(\User\Services\Grpc\WalletType::BTC)
                ])];

            return [true, $reply->getAddress()];
        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\WithdrawRepository@getUserBTCWalletHash => ' . $exception->getMessage() );
            throw $exception;
        }
    }

    private function getWithdrawRequestsByDateCollection($date_field, $from_date, $to_date, $status = null, $user_id = null)
    {
        try {
            $withdraw_profit_requests = $this->model->query();
            if ($status)
                $withdraw_profit_requests->where('status', '=', $status);

            if ($user_id)
                $withdraw_profit_requests->where('user_id', $user_id);

            $from_date = Carbon::parse($from_date)->toDateTimeString();
            $to_date = Carbon::parse($to_date)->toDateTimeString();
            return $withdraw_profit_requests->whereBetween($date_field, [$from_date, $to_date])->get();
        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\WithdrawRepository@getWithdrawRequestsByDateCollection => ' . $exception->getMessage());
            throw new \Exception(trans('wallet.responses.something-went-wrong'));
        }
    }

    private function getWithdrawRequestsHistoryByDateCollection($date_field, $from_date, $to_date, $status = null, $user_id = null)
    {
        try {
            $withdraw_profit_requests = $this->model_history->query();
            if ($status)
                $withdraw_profit_requests->where('status', '=', $status);

            if ($user_id)
                $withdraw_profit_requests->where('user_id', $user_id);

            $from_date = Carbon::parse($from_date)->toDateTimeString();
            $to_date = Carbon::parse($to_date)->toDateTimeString();
            return $withdraw_profit_requests->whereBetween($date_field, [$from_date, $to_date])->get();
        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\WithdrawRepository@getWithdrawRequestsByDateCollection => ' . $exception->getMessage());
            throw new \Exception(trans('wallet.responses.something-went-wrong'));
        }
    }

}
