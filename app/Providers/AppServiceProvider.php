<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
            \URL::forceScheme('http');
//        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
