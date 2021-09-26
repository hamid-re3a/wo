<?php

namespace Wallets\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Wallets\Mail\SettingableMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Wallets\Models\NetworkTransaction;
use Wallets\Models\WithdrawProfit;

class HandleWithdrawUnsentEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     */

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $withdraw_requests = WithdrawProfit::query()->where('status','!=', 1)->where('is_update_email_sent','=',0)->first();
        $withdraw_requests->update([
            'is_update_email_sent' => true
        ]);
        if($withdraw_requests = WithdrawProfit::query()->where('status','!=', 1)->where('is_update_email_sent','=',0)->first())
            HandleWithdrawUnsentEmails::dispatch();
    }
}
