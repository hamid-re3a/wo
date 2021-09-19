<?php

namespace Giftcode\Commands;

use Giftcode\Jobs\HandleUserForExpiredGiftcodeJob;
use Giftcode\Jobs\HandleUserForWillExpireGiftcodeJob;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use User\Models\User;

class CheckGiftcodesCommand extends Command
{

    private $wallets;
    private $ids;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'giftcode:check-giftcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check giftcodes expiration date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        User::query()->whereHas('giftcodes',function(Builder $query){
            $query->whereNull('is_expired');
            $query->whereNull('is_canceled');
            $query->whereNull('redeem_user_id');
            $query->where(function(Builder $subQuery){
               $subQuery->where('expiration_date','>' , now()->toDateTimeString());
               $subQuery->orWhereBetween('expiration_date',[now()->toDateTimeString(),now()->addWeek()->toDateTimeString()]);
            });
        })->chunkById(50,function($users){
            foreach($users AS $user) {
                HandleUserForWillExpireGiftcodeJob::dispatch($user)->delay(1);
                HandleUserForExpiredGiftcodeJob::dispatch($user)->delay(2);
            }
        });
    }
}
