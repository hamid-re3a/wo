<?php

namespace Wallets\Mail\DepositWallet;

use Bavix\Wallet\Models\Transfer;
use Wallets\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use User\Models\User;

class SenderFundEmail extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $user;
    public $transfer;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Transfer $transfer
     */
    public function __construct(User $user,Transfer $transfer)
    {
        $this->user = $user;
        $this->transfer = $transfer;
    }


    /**
     * Build the message.
     *
     * @return $this
     * @throws \Exception
     */
    public function build()
    {
        $setting = $this->getSetting();

        $fee = 'Unknown';
        if(!empty($this->transfer->withdraw->meta) AND array_key_exists('fee', $this->transfer->withdraw->meta))
            $fee = $this->transfer->withdraw->meta['fee'];

        $setting['body'] = str_replace('{{full_name}}',(is_null($this->user->full_name) || empty($this->user->full_name)) ? 'Unknown': $this->user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{amount}}',(is_null($this->transfer->deposit->amountFloat) ) ? 'Unknown': number_format($this->transfer->deposit->amountFloat,2),$setting['body']);
        $setting['body'] = str_replace('{{fee}}',number_format($fee,2),$setting['body']);
        $setting['body'] = str_replace('{{receiver_name}}',(is_null($this->transfer->deposit->payable->full_name) ) ? 'Unknown': $this->transfer->deposit->payable->full_name,$setting['body']);
        $setting['body'] = str_replace('{{receiver_member_id}}',(is_null($this->transfer->deposit->payable->member_id) ) ? 'Unknown': $this->transfer->deposit->payable->member_id,$setting['body']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($setting['subject'])
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        try {
            return walletGetEmailContent('TRANSFER_FUNDS_SENDER');
        } catch (\Throwable $exception) {
            Log::error('walletGetEmailContent [Wallets\Mail\DepositWallet\SenderFundEmail]');
            throw new $exception;
        }
    }
}
