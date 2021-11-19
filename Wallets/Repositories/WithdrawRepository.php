<?php


namespace Wallets\Repositories;


use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use Payments\Services\Processors\PayoutFacade;
use Payments\Services\Processors\PayoutProcessor;
use User\Models\User;
use User\Services\GatewayClientFacade;
use User\Services\Grpc\UserTransactionPassword;
use User\Services\Grpc\WalletInfo;
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
                throw new \Exception(trans('wallet.withdraw-profit-request.withdrawal-requests-is-not-active'),400);

            $user = array_key_exists('user_id', $request) ? User::query()->find($request['user_id']) : auth()->user();
            $request['user_id'] = $user->id;

            //Check Transaction Password if request is not for reverting
            if ($user->id == auth()->user()->id AND !array_key_exists('is_revert',$request)) {
                if (!isset($request['transaction_password'])) {
                    Log::error('Wallets\Repositories\WithdrawRepository@makeWithdrawRequest => Undefined transaction password for user in request body');
                    throw new \Exception(trans('wallets.responses.something-went-wrong'), 400);
                }

                $password_request = new UserTransactionPassword();
                $password_request->setUserId((int)$user->id);
                $password_request->setTransactionPassword((string)$request['transaction_password']);
                $acknowledge = GatewayClientFacade::checkTransactionPassword($password_request);

                if (!$acknowledge->getStatus())
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'transaction_password' => [trans('wallet.withdraw-profit-request.incorrect-transaction-password')],
                    ]);
            }

            /**@var $user User */
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
            $wallet_hash = null;
            //Check if request is revert or NOT
            if (array_key_exists('is_revert', $request) AND isset($request['is_revert'])) {
                $crypto_rate = $request['crypto_rate'];
                $wallet_hash = $request['wallet_hash'];
            } else {
                $crypto_rate = $this->getBtcPrice();
            }

            list($total, $fee) = calculateWithdrawalFee($request['amount'], $request['currency']);
            //Check user balance again
            if ($this->bankService->getBalance($this->user_wallet) < $total)
                throw new \Exception(trans('wallet.responses.not-enough-balance'), 406);

            $withdraw_transaction = $this->bankService->withdraw($this->user_wallet, $request['amount'], null, 'Withdrawal request', null, true, false);
            $withdraw_fee = $this->bankService->withdraw($this->user_wallet, $fee, null, 'Withdrawal request fee', null);

            if (!$wallet_hash) {
                list($flag, $wallet_hash) = $this->getUserBTCWalletHash($request['user_id']);
                if (!$flag) {
                    Log::error('Wallets\Repositories\WithdrawRepository@withdrawBTC =>' . $wallet_hash);
                    throw new \Exception($wallet_hash, 406);
                }
            }

            return WithdrawProfit::create([
                'user_id' => $request['user_id'],
                'withdraw_transaction_id' => $withdraw_transaction->id,
                'wallet_hash' => $wallet_hash,
                'payout_service' => 'btc-pay-server', //TODO improvements or like payment drivers ?!
                'currency' => $request['currency'],
                'pf_amount' => $request['amount'],
                'fee' => (double)$fee,
                'crypto_amount' => pfToUsd((double)$request['amount']) / $crypto_rate,
                'crypto_rate' => $crypto_rate
            ]);

        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\withdrawBTC => ' . $exception->getMessage());
            Log::error('Wallets\Repositories\withdrawBTC => ' . serialize($request));
            throw $exception;
        }

    }

    public function update(array $validated_request, Collection $withdrawProfitRequests)
    {
        try {
            switch ($validated_request['status']) {
                case WALLET_WITHDRAW_COMMAND_PROCESS :
                    $this->pay($withdrawProfitRequests->where('status', '=', WALLET_WITHDRAW_REQUEST_STATUS_UNDER_REVIEW_STRING));
                    break;
                case WALLET_WITHDRAW_COMMAND_REJECT :
                    $this->rejectWithdrawRequest($validated_request, $withdrawProfitRequests);
                    break;
                case WALLET_WITHDRAW_COMMAND_REVERT :
                    $this->revertWithdrawRequests($withdrawProfitRequests);
                    break;
                case WALLET_WITHDRAW_COMMAND_POSTPONE:
                    $this->updateModel($withdrawProfitRequests->where('status', WALLET_WITHDRAW_REQUEST_STATUS_UNDER_REVIEW_STRING)->pluck('id')->toArray(), [
                        'status' => WALLET_WITHDRAW_COMMAND_POSTPONE,
                        'postponed_to' => Carbon::parse($validated_request['postponed_to'])->toDateTimeString(),
                        'act_reason' => isset($validated_request['act_reason']) ? $validated_request['act_reason'] : null
                    ]);
                    break;
            }
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function updateModel(array $ids, array $validated_data)
    {
        $this->model->query()->whereIn('id', $ids)->update($validated_data);
    }

    public function pay($withdrawProfits, $dispatchType = 'dispatchSync')
    {
        try {

            list($flag,$response) = PayoutFacade::pay($withdrawProfits,$dispatchType);
            if(!$flag)
                throw new \Exception($response,406);

        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function rejectWithdrawRequest(array $request, Collection $withdraw_requests)
    {
        foreach ($withdraw_requests AS $withdraw_request) {
            $admin_bank_service = new BankService(User::query()->find(1));
            $admin_balance = $admin_bank_service->getBalance(WALLET_NAME_DEPOSIT_WALLET);
            if ($admin_balance < $withdraw_request->fee) {
                //TODO Notify admin
                Log::error('Wallets\Repositories\WithdrawRepository@rejectWithdrawRequest => Insufficient PF in admin Deposit Wallet');
                Log::error('Admin Balance => ' . $admin_balance . ' | Fee => ' . $withdraw_request->fee . ' | WithdrawalID => ' . $withdraw_request->id);
                throw new \Exception(trans('wallet.responses.something-went-wrong'), 500);
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
    }

    public function revertWithdrawRequests(Collection $withdrawProfitRequests)
    {
        try {
            $records_have_to_update = [];
            foreach ($withdrawProfitRequests AS $withdrawProfitRequest) {
                //Check if request is marked as rejected
                if ($withdrawProfitRequest->getRawOriginal('status') != WALLET_WITHDRAW_COMMAND_REJECT)
                    throw new Exception(trans('wallet.withdraw-profit-request.you-can-revert-a-rejected-request', [
                        'uuid' => $withdrawProfitRequest->uuid
                    ]), 406);
                //Make a fresh withdraw request
                $request = $withdrawProfitRequest->toArray();
                $request['is_revert'] = true;
                $request['amount'] = $request['pf_amount'];
                $this->makeWithdrawRequest($request);
                $records_have_to_update[] = $withdrawProfitRequest->id;
            }
            $this->updateModel($records_have_to_update, [
                'status' => WALLET_WITHDRAW_COMMAND_REVERT,
                'is_update_email_sent' => true //Ignore sending update email
            ]);
        } catch (\Throwable $exception) {
            Log::error('Wallets\Repositories\WithdrawRepository@revertWithdrawRequests => ' . $exception->getMessage());
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
            throw new \Exception(trans('wallet.responses.something-went-wrong'),400);
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
            throw new \Exception(trans('wallet.responses.something-went-wrong'),400);
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
            Log::error('Wallets\Repositories\WithdrawRepository@getUserBTCWalletHash => ' . $exception->getMessage());
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
            throw new \Exception(trans('wallet.responses.something-went-wrong'),400);
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
            throw new \Exception(trans('wallet.responses.something-went-wrong'),400);
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getBtcPrice()
    {
        $btc_price = Http::get('https://blockchain.info/ticker');
        if ($btc_price->ok() AND is_array($btc_price->json()) AND isset($btc_price->json()['USD']['15m'])) {
            $crypto_rate = $btc_price->json()['USD']['15m'];
        } else {
            throw new \Exception(trans('wallet.withdraw-profit-request.external-resource-error', [
                'server' => 'BlockChain.info'
            ]), 500);
        }
        return $crypto_rate;
    }

}
