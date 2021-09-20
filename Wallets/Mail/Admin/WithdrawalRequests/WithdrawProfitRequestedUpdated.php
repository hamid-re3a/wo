<?php

namespace Wallets\Mail\Admin\WithdrawalRequests;

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
        $this->withdrawRequest->refresh();
        $setting['body'] = str_replace('{{uuid}}',empty($this->withdrawRequest->uuid) ? 'Unknown': $this->withdrawRequest->uuid,$setting['body']);
        $setting['body'] = str_replace('{{full_name}}',empty($this->withdrawRequest->user_id) ? 'Unknown': $this->withdrawRequest->user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{amount_in_pf}}',empty($this->withdrawRequest->withdraw_transaction_id) ? 'Unknown': walletPfAmount((int)abs($this->withdrawRequest->withdrawTransaction->amountFloat)),$setting['body']);
        $setting['body'] = str_replace('{{amount_in_btc}}',empty($this->withdrawRequest->withdraw_transaction_id) ? 'Unknown': walletPfAmount((int)abs($this->withdrawRequest->withdrawTransaction->amountFloat)),$setting['body']);
        $setting['body'] = str_replace('{{created_at}}',empty($this->withdrawRequest->created_at) ? 'Unknown': $this->withdrawRequest->created_at,$setting['body']);
        $setting['body'] = str_replace('{{updated_at}}',empty($this->withdrawRequest->updated_at) ? 'Unknown': $this->withdrawRequest->updated_at,$setting['body']);
        $setting['body'] = str_replace('{{withdraw_transaction_uuid}}',empty($this->withdrawRequest->withdraw_transaction_id) ? 'Unknown': $this->withdrawRequest->withdrawTransaction->uuid ,$setting['body']);
        $setting['body'] = str_replace('{{refund_transaction_uuid}}',empty($this->withdrawRequest->refund_transaction_id) ? 'Unknown': $this->withdrawRequest->refundTransaction->uuid ,$setting['body']);
        $setting['body'] = str_replace('{{rejection_reason}}',empty($this->withdrawRequest->rejection_reason) ? 'Unknown': $this->withdrawRequest->rejection_reason ,$setting['body']);
        $setting['body'] = str_replace('{{network_hash}}',empty(($this->withdrawRequest->network_hash)) ? 'Unknown': $this->withdrawRequest->network_hash ,$setting['body']);

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

            return walletGetEmailContent($key);
        } catch (\Throwable $exception) {
            Log::error('walletGetEmailContent [Wallets\Mail\Admin\WithdrawalRequests\WithdrawProfitRequestedUpdate]');
            throw $exception;
        }
    }
}
