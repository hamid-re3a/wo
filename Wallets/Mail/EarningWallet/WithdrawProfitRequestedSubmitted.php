<?php

namespace Wallets\Mail\EarningWallet;

use Illuminate\Support\Facades\Http;
use Wallets\Jobs\UrgentEmailJob;
use Wallets\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Wallets\Models\WithdrawProfit;

class WithdrawProfitRequestedSubmitted extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $withdrawRequest;

    /**
     * Create a new message instance.
     *
     * @param WithdrawProfit $withdrawRequest
     */
    public function __construct(WithdrawProfit $withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest;
    }


    /**
     * Build the message.
     *
     * @return $this
     * @throws \Throwable
     */
    public function build()
    {
        $setting = $this->getSetting();

        $setting['body'] = str_replace('{{uuid}}',empty($this->withdrawRequest->uuid) ? 'Unknown': $this->withdrawRequest->uuid,$setting['body']);
        $setting['body'] = str_replace('{{full_name}}',(is_null($this->withdrawRequest->user->full_name) || empty($this->withdrawRequest->user->full_name)) ? 'Unknown': $this->withdrawRequest->user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{amount_in_pf}}',(is_null(abs($this->withdrawRequest->withdrawTransaction->amountFloat)) ) ? 'Unknown': formatCurrencyFormat((int)abs($this->withdrawRequest->withdrawTransaction->amountFloat)),$setting['body']);
        $setting['body'] = str_replace('{{amount_in_btc}}',(is_null($this->withdrawRequest->crypto_amount) ) ? 'Unknown': $this->withdrawRequest->crypto_amount,$setting['body']);
        $setting['body'] = str_replace('{{created_at}}',(is_null($this->withdrawRequest->created_at) ) ? 'Unknown': $this->withdrawRequest->created_at,$setting['body']);
        $setting['body'] = str_replace('{{withdraw_transaction_uuid}}',(is_null($this->withdrawRequest->withdrawTransaction->uuid) ) ? 'Unknown': $this->withdrawRequest->withdrawTransaction->uuid ,$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        try {
            return walletGetEmailContent('WITHDRAW_REQUEST_SUBMITTED');
        } catch (\Throwable $exception) {
            Log::error('walletGetEmailContent [Wallets\Mail\EarningWallet\WithdrawProfitRequestedSubmitted]');
            throw $exception;
        }
    }
}
