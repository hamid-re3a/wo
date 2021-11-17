<?php

namespace Payments\Services\Processors;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Payments\Jobs\ProcessBTCPayServerPayoutsJob;
use Wallets\Models\WithdrawProfit;

class PayoutProcessor
{

    /**
     * pay by every payment Driver
     * @inheritDoc
     */
    public function pay(Collection $payout_requests,string $dispatchType = 'dispatch'): array
    {
        try {
            list($currency, $amount) = $this->checkWithdrawRequestsForPayOut($payout_requests);
            $this->checkBPSWalletBalance($amount);

            switch ($currency) {
                case 'BTC':
                    return $this->payoutBtc($payout_requests,$dispatchType);
                default:
                    break;
            }

            return [false,trans('payment.responses.something-went-wrong')];
        } catch (\Throwable $exception) {
            Log::error('Payments\Services\Processors\@pay => ' . $exception->getMessage());
            throw $exception;
        }
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


    public function checkBPSWalletBalance($amount) : void
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


    private function payoutBtc($payout_requests,$dispatchType)
    {
        ProcessBTCPayServerPayoutsJob::$dispatchType($payout_requests,$dispatchType);
        return [true,null];
    }

}
