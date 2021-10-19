<?php


namespace Wallets\Repositories;


use Giftcode\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Payments\Services\Processors\PayoutProcessor;
use User\Models\User;
use User\Services\Grpc\WalletInfo;
use User\Services\Grpc\WalletType;
use Wallets\Models\Transaction;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;

class WithdrawRepository
{
    /**
     * @var $bankService BankService
     *
     */
    private $bankService;
    private $payout_processor;
    private $wallet;
    /**@var $withdraw_profits WithdrawProfit */
    private $model;

    public function __construct(PayoutProcessor $payout_processor)
    {
        $this->payout_processor = $payout_processor;
        $this->model = new WithdrawProfit();
    }

    public function makeWithdrawRequest(Request $request)
    {
        if (!getWalletSetting('withdrawal_request_is_enabled'))
            throw new \Exception(trans('wallet.withdraw-profit-request.withdrawal-requests-is-not-active'));

        /**@var $user User */
        $user = auth()->user();
        $this->bankService = new BankService($user);
        $this->wallet = config('earningWallet');
        $this->bankService->getWallet($this->wallet);

        try {
            switch ($request->get('currency')) {
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

    public function withdrawBTC(Request $request)
    {
        try {
            $btc_price = Http::get('https://blockchain.info/ticker');

            if ($btc_price->ok() AND is_array($btc_price->json()) AND isset($btc_price->json()['USD']['15m'])) {

                $withdraw_transaction = $this->bankService->withdraw($this->wallet, $request->get('amount'), null, 'Withdraw request', null);
                return WithdrawProfit::query()->create([
                    'user_id' => auth()->user()->id,
                    'withdraw_transaction_id' => $withdraw_transaction->id,
                    'wallet_hash' => $this->getUserBTCWalletHash(),
                    'payout_service' => 'btc-pay-server', //TODO improvements or like payment drivers ?!
                    'currency' => $request->get('currency'),
                    'pf_amount' => $request->get('amount'),
                    'crypto_amount' => $request->get('amount') / $btc_price->json()['USD']['15m'],
                    'crypto_rate' => $btc_price->json()['USD']['15m']
                ]);
            } else {

                throw new \Exception(trans('wallet.withdraw-profit-request.external-resource-error', [
                    'server' => 'BlockChain.info'
                ]), 406);

            }
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function update(array $validated_request, WithdrawProfit $withdrawProfit)
    {
        return $withdrawProfit->update($validated_request);
    }

    public function pay($withdrawProfits,$dispatchType = 'dispatchSync')
    {
        try {
            if (is_array($withdrawProfits) AND array_key_exists('currency', $withdrawProfits)){
                $currency = $withdrawProfits['currency'];
                $amount = $withdrawProfits['crypto_amount'];
                $withdrawProfits = collect()->push($withdrawProfits);
            }
            else if ($withdrawProfits instanceof Collection)
                list($currency,$amount) = $this->checkWithdrawRequestsForPayOut($withdrawProfits);
            else
                throw new \Exception(trans('wallet.responses.something-went-wrong'));

            $this->checkBPSWalletBalance($amount);
            $this->payout_processor->pay($currency, $withdrawProfits,$dispatchType);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function rejectWithdrawRequest(Request $request, WithdrawProfit $withdraw_request)
    {
        try {
            //Withdraw Admin deposit wallet
            $bank_service = new BankService(User::query()->first());
            $bank_service->withdraw(config('depositWallet'), $withdraw_request->pf_amount, [
                'description' => 'Refund withdrawal request',
                'withdrawal_request_id' => $withdraw_request->id
            ], 'Withdrawal refund');

            //Deposit user wallet
            $user = $withdraw_request->user()->first();
            $bank_service = new BankService($user);
            $refund_transaction = $bank_service->deposit(config('earningWallet'), $withdraw_request->pf_amount, [
                'description' => 'Withdrawal request rejected',
                'withdrawal_request_id' => $withdraw_request->id,
            ], true, 'Withdrawal refund');


            $this->update([
                'status' => $request->get('status'),
                'rejection_reason' => $request->has('rejection_reason') ? $request->get('rejection_reason') : $withdraw_request->rejection_reason,
                'actor_id' => auth()->user()->id,
                'refund_transaction_id' => $refund_transaction instanceof Transaction ? $refund_transaction->id : $withdraw_request->refund_transaction_id
            ], $withdraw_request);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function checkWithdrawRequestsForPayOut(Collection $withdraw_requests)
    {
        try {
            /**
             * @var $first_request WithdrawProfit
             */
            $first_request = $withdraw_requests->first();
            if ($withdraw_requests->where('currency', '!=', $first_request->currency)->count())
                throw new \Exception(trans('wallet.withdraw-profit-request.different-currency-payout'));

            return [$first_request->currency,$withdraw_requests->sum('crypto_amount')];
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function getPendingAmountVsTimeChart($type,$user_id = null)
    {
        try {
            $that = $this;
            $function_withdraw_requests = function($from_date,$to_date) use ($that,$user_id){
                return $that->getWithdrawRequestsByDateCollection('created_at',$from_date,$to_date,WITHDRAW_COMMAND_UNDER_REVIEW,$user_id);
            };

            $sub_function = function ($collection, $intervals) {
                /**@var $collection Collection*/
                return $collection->whereBetween('created_at', $intervals)->sum(function($withdraw_request) {
                    /**@var $withdraw_request WithdrawProfit*/
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

    public function getPaidAmountVsTimeChart($type,$user_id = null)
    {
        try {
            $that = $this;
            $function_withdraw_requests = function($from_date,$to_date) use ($that,$user_id){
                return $that->getWithdrawRequestsByDateCollection('created_at',$from_date,$to_date,WITHDRAW_COMMAND_PROCESS,$user_id);
            };

            $sub_function = function ($collection, $intervals) {
                /**@var $collection Collection*/
                return $collection->whereBetween('created_at', $intervals)->sum(function($withdraw_request) {
                    /**@var $withdraw_request WithdrawProfit*/
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
                throw new \Exception(trans('wallet.withdraw-profit-request.check-bps-or-blockchain.com-server', [
                    'server' => 'BTCPayServer'
                ]));
            }
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    private function getUserBTCWalletHash()
    {
//        return 'tb1qn6whdz3fw4f7unnvvkphgpxg58jl3mcyjreya9';
        if (app()->environment('testing'))
            return 'grpc called';

        $client = new \User\Services\Grpc\UserServiceClient(env('API_GATEWAY_GRPC_URL', 'staging-api-gateway.janex.org:9595'), [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
        $req = app(\User\Services\Grpc\WalletRequest::class);
        $req->setUserId((int)auth()->user()->id);
        $req->setWalletType(\User\Services\Grpc\WalletType::BTC);
        /**@var $reply WalletInfo */
        list($reply, $status) = $client->getUserWalletInfo($req)->wait();
        if (!$status OR $status->code != 0 OR !$reply->getAddress())
            throw new \Exception(trans('wallet.withdraw-profit-request.cant-find-wallet-address', [
                'name' => WalletType::name(\User\Services\Grpc\WalletType::BTC)
            ]));

        return $reply->getAddress();
    }

    private function getWithdrawRequestsByDateCollection($date_field,$from_date,$to_date,$status = null,$user_id = null)
    {
        try {
            $withdraw_profit_requests = $this->model->query();
            if($status)
                $withdraw_profit_requests->where('status','=',WITHDRAW_COMMAND_UNDER_REVIEW);

            if($user_id)
                $withdraw_profit_requests->where('user_id',$user_id);

            $from_date = Carbon::parse($from_date)->toDateTimeString();
            $to_date = Carbon::parse($to_date)->toDateTimeString();
            return $withdraw_profit_requests->whereBetween($date_field, [$from_date,$to_date])->get();
        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\WithdrawRepository@getWithdrawRequestsByDateCollection => ' . $exception->getMessage());
            throw new \Exception(trans('wallet.responses.something-went-wrong'));
        }
    }

}
