<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use User\Services\Grpc\WalletType;
use Wallets\Http\Requests\Front\CreateWithdrawRequest;
use Wallets\Http\Resources\WithdrawProfitResource;
use Wallets\Models\WithdrawProfit;
use Wallets\Services\BankService;

class WithdrawRequestController extends Controller
{
    private $bankService;
    private $wallet;

    private function prepareEarningWallet()
    {

        $this->bankService = new BankService(auth()->user());
        $this->wallet = config('earningWallet');
        $this->bankService->getWallet($this->wallet);

    }


    /**
     * Withdraw requests
     * @group Public User > Withdraw Requests
     */
    public function withdraw_requests()
    {
        try {
            return api()->success(null, WithdrawProfitResource::collection(auth()->user()->withdrawRequests()->simplePaginate())->response()->getData());
        } catch (\Throwable $exception) {
            Log::error('EarningWalletController@withdraw_requests => ' . serialize(request()->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Withdraw request preview
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     */
    public function create_withdraw_request_preview(CreateWithdrawRequest $request)
    {
        try {

            $this->withdrawalRequestIsActive();
            //Grpc get wallet hash
            $wallet_hash = $this->getUserWalletHash();
            $btc_price = Http::get('https://blockchain.info/ticker');

            if($btc_price->ok() AND is_array($btc_price->json()) AND isset($btc_price->json()['USD']['15m'])) {

                return api()->success(null,[
                    'pf_amount' => walletPfAmount($request->get('amount')),
                    'crypto_amount' => $request->get('amount') / $btc_price['USD']['15m'],
                    'wallet_hash' => $wallet_hash
                ]);

            } else {
                return api()->error(null,[
                    'subject' => trans('wallet.withdraw-profit-request.check-bps-or-blockchain.com-server', [
                        'server' => 'BlockChain.info'
                    ])
                ],406);
            }

        } catch (\Throwable $exception) {
            Log::error('EarningWalletController@create_withdraw_request => ' . serialize($request->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Submit withdraw request
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function create_withdraw_request(CreateWithdrawRequest $request)
    {

        try {
            DB::beginTransaction();
            $this->withdrawalRequestIsActive();
            $this->prepareEarningWallet();

            $withdraw_transaction = $this->bankService->withdraw($this->wallet, $request->get('amount'),null,true,'Withdraw request');

            $wallet_hash = $this->getUserWalletHash();
            $btc_price = Http::get('https://blockchain.info/ticker');

            if($btc_price->ok() AND is_array($btc_price->json()) AND isset($btc_price->json()['USD']['15m'])) {

                $withdraw_request = WithdrawProfit::query()->create([
                    'user_id' => auth()->user()->id,
                    'withdraw_transaction_id' => $withdraw_transaction->id,
                    'wallet_hash' => $wallet_hash,
                    'currency' => $request->get('currency'),
                    'pf_amount' => $request->get('amount'),
                    'crypto_amount' => $request->get('amount') / $btc_price['USD']['15m'],
                ]);

            } else {
                return api()->error(null,[
                    'subject' => trans('wallet.withdraw-profit-request.check-bps-or-blockchain.com-server', [
                        'server' => 'BlockChain.info'
                    ])
                ],406);
            }



            DB::commit();
            return api()->success(null, WithdrawProfitResource::make($withdraw_request->refresh()));
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('EarningWalletController@create_withdraw_request => ' . serialize($request->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
        }
    }

    private function getUserWalletHash()
    {
//        return 'tb1qn6whdz3fw4f7unnvvkphgpxg58jl3mcyjreya9';
        $client = new \User\Services\Grpc\UserServiceClient('staging-api-gateway.janex.org:9595', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
        $req = app(\User\Services\Grpc\WalletRequest::class);
        $req->setUserId(auth()->user()->id);
        $req->setWalletType(\User\Services\Grpc\WalletType::BTC);
        list($reply, $status) = $client->getUserWalletInfo($req)->wait();
        if(!$status->code != 0 OR !$reply->getAddress())
            throw new \Exception(trans('wallet.withdraw-profit-request.cant-find-wallet-address',[
                'name' => WalletType::name(0)
            ]));

        return $reply->getAddress();
    }

    private function withdrawalRequestIsActive()
    {
        if(!walletGetSetting('withdrawal_request_is_enabled'))
            throw new \Exception(trans('wallet.withdraw-profit-request.withdrawal-requests-is-not-active'));
    }
}
