<?php

namespace Wallets\Mail\DepositWallet;

use Bavix\Wallet\Models\Transfer;
use Wallets\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use User\Models\User;

class RequestFundEmail extends Mailable implements SettingableMail
{
    use Queueable, SerializesModels;

    public $user;
    public $friend_user;
    public $amount;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param User $friend_user
     * @param int $amount
     */
    public function __construct(User $user,User $friend_user,int $amount)
    {
        $this->user = $user;
        $this->friend_user = $friend_user;
        $this->amount = $amount;
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

        $setting['body'] = str_replace('{{full_name}}',(is_null($this->user->full_name) || empty($this->user->full_name)) ? 'Unknown': $this->user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{amount}}',(is_null($this->amount) ) ? 'Unknown': number_format($this->amount,2),$setting['body']);
        $setting['body'] = str_replace('{{request_full_name}}',(is_null($this->friend_user->full_name) ) ? 'Unknown': $this->friend_user->full_name,$setting['body']);
        $setting['body'] = str_replace('{{app_name}}',config('APP_NAME','R2F'),$setting['body']);
        $subject = str_replace('{{request_first_name}}', (is_null($this->friend_user->first_name) ) ? 'Unknown': $this->friend_user->first_name, $setting['subject']);

        return $this
            ->from($setting['from'], $setting['from_name'])
            ->subject($subject)
            ->html( $setting['body']);
    }

    public function getSetting() : array
    {
        try {
            return walletGetEmailContent('PAYMENT_REQUEST');
        } catch (\Throwable $exception) {
            Log::error('walletGetEmailContent [Wallets\Mail\DepositWallet\RequestFundEmail]');
            throw $exception;
        }
    }
}
