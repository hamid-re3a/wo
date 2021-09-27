<?php


namespace Wallets\Repositories;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use User\Models\User;
use User\Services\Grpc\WalletInfo;
use User\Services\Grpc\WalletType;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;

class WithdrawRepository
{
    private $bankService;
    private $wallet;

    public function makeWithdrawRequest(Request $request)
    {
        if (!walletGetSetting('withdrawal_request_is_enabled'))
            throw new \Exception(trans('wallet.withdraw-profit-request.withdrawal-requests-is-not-active'));

        /**@var $user User*/
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

                $withdraw_transaction = $this->bankService->withdraw($this->wallet, $request->get('amount'), null, true, 'Withdraw request');
                return WithdrawProfit::query()->create([
                    'user_id' => auth()->user()->id,
                    'withdraw_transaction_id' => $withdraw_transaction->id,
                    'wallet_hash' => $this->getUserBTCWalletHash(),
                    'payout_service' => 'btc-pay-server', //TODO improvements or like payment drivers ?!
                    'currency' => $request->get('currency'),
                    'pf_amount' => $request->get('amount'),
                    'crypto_amount' => $request->get('amount') / $btc_price['USD']['15m'],
                    'crypto_rate' => $btc_price['USD']['15m']
                ]);
            } else {

                throw new \Exception(trans('wallet.withdraw-profit-request.external-resource-error', [
                    'server' => 'BlockChain.info'
                ]), 406);

            }
        }catch (\Throwable $exception) {
            throw $exception;
        }
    }

    private function getUserBTCWalletHash()
    {
        $client = new \User\Services\Grpc\UserServiceClient('staging-api-gateway.janex.org:9595', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
        $req = app(\User\Services\Grpc\WalletRequest::class);
        Log::info('request user BTC wallet => ' . auth()->user()->id);
        $req->setUserId((int)auth()->user()->id);
        $req->setWalletType(\User\Services\Grpc\WalletType::BTC);
        /**@var $reply WalletInfo*/
        list($reply, $status) = $client->getUserWalletInfo($req)->wait();
        Log::info(serialize('reply => ' . serialize($reply)));
        Log::info(serialize('$status =>' . serialize($status)));
        if (!$status->code != 0 OR !$reply->getAddress())
            throw new \Exception(trans('wallet.withdraw-profit-request.cant-find-wallet-address', [
                'name' => WalletType::name(\User\Services\Grpc\WalletType::BTC)
            ]));

        return $reply->getAddress();
    }
}
