<?php

namespace Giftcode\Jobs;

use Giftcode\Mail\User\GiftcodeWillExpireSoonEmail;
use Giftcode\Models\Giftcode;
use Giftcode\Repository\GiftcodeRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use User\Models\User;

class HandleUserForExpiredGiftcodeJob implements ShouldQueue
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
                ->where('expiration_date', '>', now()->toDateTimeString())
                ->get();

        if($giftcodes_will_expire->count() > 0) {
            $giftcodeRepository = app(GiftcodeRepository::class);

            foreach ($giftcodes_will_expire AS $giftcode) {
                try {
                    $giftcodeRepository->expire($giftcode);
                } catch (\Throwable $exception) {
                    Log::error('HandleUserForExpiredGiftcodeJob error');
                }
            }
        }

    }
}
