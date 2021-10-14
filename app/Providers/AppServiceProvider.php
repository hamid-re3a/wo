<?php

namespace App\Providers;

use App\Notifications\QueueFailed;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobFailed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
//        if(config('app.env') === 'production') {
            \URL::forceScheme('https');
//        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $slackUrl = env('SLACK_WEBHOOK_URL');
        Queue::failing(function (JobFailed $event) use ($slackUrl) {
            Notification::route('slack', $slackUrl)->notify(new QueueFailed($event));
        });
    }
}
