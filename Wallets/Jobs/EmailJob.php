<?php

namespace Wallets\Jobs;

use Wallets\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     */
    private $email;
    private $email_address;

    /**
     * Create a new job instance.
     *
     * @param SettingableMail $email
     * @param string $email_address
     */
    public function __construct(SettingableMail $email, $email_address)
    {
        $this->queue = env('EMAIL_QUEUE_NAME','subscription_emails');
        $this->email = $email;
        $this->email_address = $email_address;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->email->getSetting()['is_active'])
            Mail::to($this->email_address)->send($this->email);
    }
}
