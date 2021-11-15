<?php

namespace Giftcode\Jobs;

use Giftcode\Mail\User\GiftcodeExpiredEmail;
use Giftcode\Mail\User\GiftcodeWillExpireSoonEmail;
use Giftcode\Models\Giftcode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use User\Models\User;

class HandleUserForWillExpireGiftcodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Mailable
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->queue = env('DEFAULT_QUEUE_NAME','default');
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $giftcodes_will_expire =
            Giftcode::query()->where('user_id', '=', $this->user->id)
                ->whereNull('is_expired')
                ->whereNull('is_canceled')
                ->whereNull('redeem_user_id')
                ->whereBetween('expiration_date', [now()->toDateTimeString(), now()->addWeek()->toDateTimeString()])
                ->get();


        foreach ($giftcodes_will_expire AS $giftcode)
            TrivialEmailJob::dispatch(new GiftcodeWillExpireSoonEmail($giftcode), $this->user->email);


    }
}
