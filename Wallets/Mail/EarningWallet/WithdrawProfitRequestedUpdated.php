<?php

namespace Wallets\Mail\EarningWallet;

use Wallets\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Wallets\Models\WithdrawProfit;

class WithdrawProfitRequestedUpdated extends Mailable implements SettingableMail
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
     * @throws \Exception
     * @throws \Throwable
     */
    public function build()
    {
        $setting = $this->getSetting();

        $setting['body'] = str_replace('{{uuid}}',empty($this->withdrawRequest->uuid) ? 'Unknown': $this->withdrawRequest->uuid,$setting['body']);
        $setting['body'] = str_replace('{{full_name}}',empty($this->withdrawRequest->user_id) ? 'Unknown': $this->withdrawRequest->user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{amount_in_pf}}',empty($this->withdrawRequest->withdraw_transaction_id) ? 'Unknown': formatCurrencyFormat((int)abs($this->withdrawRequest->withdrawTransaction->amountFloat)),$setting['body']);
        $setting['body'] = str_replace('{{amount_in_btc}}',(is_null($this->withdrawRequest->crypto_amount) ) ? 'Unknown': $this->withdrawRequest->crypto_amount,$setting['body']);
        $setting['body'] = str_replace('{{created_at}}',empty($this->withdrawRequest->created_at) ? 'Unknown': $this->withdrawRequest->created_at,$setting['body']);
        $setting['body'] = str_replace('{{updated_at}}',empty($this->withdrawRequest->updated_at) ? 'Unknown': $this->withdrawRequest->updated_at,$setting['body']);
        $setting['body'] = str_replace('{{updated_at}}',empty($this->withdrawRequest->postponed_to) ? 'Unknown': $this->withdrawRequest->postponed_to,$setting['body']);
        $setting['body'] = str_replace('{{withdraw_transaction_uuid}}',empty($this->withdrawRequest->withdraw_transaction_id) ? 'Unknown': $this->withdrawRequest->withdrawTransaction->uuid ,$setting['body']);
        $setting['body'] = str_replace('{{refund_transaction_uuid}}',empty($this->withdrawRequest->refund_transaction_id) ? 'Unknown': $this->withdrawRequest->refundTransaction->uuid ,$setting['body']);
        $setting['body'] = str_replace('{{rejection_reason}}',empty($this->withdrawRequest->rejection_reason) ? 'Unknown': $this->withdrawRequest->rejection_reason ,$setting['body']);
        $setting['body'] = str_replace('{{network_hash}}',empty(($this->withdrawRequest->network_transaction_id)) ? 'Unknown': $this->withdrawRequest->networkTransaction->transaction_hash ,$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        try {
            $key = 'WITHDRAW_REQUEST_PROCESSED';
            if($this->withdrawRequest->getRawOriginal('status') == 2)
                $key = 'WITHDRAW_REQUEST_REJECTED';
            if($this->withdrawRequest->getRawOriginal('status') == 4)
                $key = 'WITHDRAW_REQUEST_POSTPONED';

            return walletGetEmailContent($key);
        } catch (\Throwable $exception) {
            Log::error('walletGetEmailContent [Wallets\Mail\EarningWallet\WithdrawProfitRequestedUpdate]');
            throw $exception;
        }
    }
}
