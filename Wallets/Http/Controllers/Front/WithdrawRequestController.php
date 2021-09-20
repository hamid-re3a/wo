<?php

namespace Wallets\Http\Controllers\Front;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * Create withdraw request preview
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     */
    public function create_withdraw_request_preview(CreateWithdrawRequest $request)
    {
        try {

            //Grpc get wallet hash
            $wallet_hash = $this->getUserWalletHash();

            return api()->success(null,[
                'amount' => $request->get('amount'),
                'wallet_hash' => $wallet_hash
            ]);
        } catch (\Throwable $exception) {
            Log::error('EarningWalletController@create_withdraw_request => ' . serialize($request->all()));
            return api()->error(null, [
                'subject' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Create withdraw request
     * @group Public User > Withdraw Requests
     * @param CreateWithdrawRequest $request
     * @return JsonResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function create_withdraw_request(CreateWithdrawRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->prepareEarningWallet();

            $withdraw_transaction = $this->bankService->withdraw($this->wallet, $request->get('amount'),'Withdraw request');


            $wallet_hash = $this->getUserWalletHash();

            $withdraw_request = WithdrawProfit::query()->create([
                'user_id' => auth()->user()->id,
                'withdraw_transaction_id' => $withdraw_transaction->id,
                'wallet_hash' => $wallet_hash
            ]);

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
        $client = new \User\Services\Grpc\UserServiceClient('staging-api-gateway.janex.org:9595', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure()
        ]);
        $req = app(\User\Services\Grpc\WalletRequest::class);
        $req->setUserId(auth()->user()->id);
        $req->setWalletType(\User\Services\Grpc\WalletType::BTC);
        list($reply, $status) = $client->getUserWalletInfo($req)->wait();
        if(!$status OR !$reply->getAddress())
            throw new \Exception(trans('wallet.responses.something-went-wrong'));

        return $reply->getAddress();
    }
}
