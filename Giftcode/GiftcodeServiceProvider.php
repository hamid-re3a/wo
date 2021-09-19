<?php

namespace Giftcode;

use Giftcode\Commands\CheckGiftcodesCommand;
use Giftcode\Models\EmailContent;
use Giftcode\Models\Giftcode;
use Giftcode\Models\Setting;
use Giftcode\Observers\EmailContentObserver;
use Giftcode\Observers\GiftcodeObserver;
use Giftcode\Observers\SettingObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class GiftcodeServiceProvider extends ServiceProvider
{
    private $routes_namespace = 'Giftcode\Http\Controllers';
    private $namespace = 'Giftcode';
    private $name = 'giftcode';
    private $config_file_name = 'giftcode';

    /**
     * Register API class.
     *
     * @return void
     */
    public function register()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }
        if ($this->shouldMigrate()) {
            $this->loadMigrationsFrom([
                __DIR__ . '/database/migrations',
            ]);
        }

    }

    /**
     * Bootstrap API resources.
     *
     * @return void
     */
    public function boot()
    {

        $this->setupConfig();

        $this->registerHelpers();

        $this->registerObservers();

        $this->registerCommands();

        Route::prefix('v1/giftcode')
            ->middleware('api')
            ->namespace($this->routes_namespace)
            ->group(__DIR__ . '/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->seed();
        }

        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang'),
        ], 'user-resources');

        $this->publishes([
            __DIR__ . '/config/' => config_path(),
        ]);
    }

    /**
     * Set Config files.
     */
    private function setupConfig()
    {
        $path = realpath($raw = __DIR__ . '/config/' . $this->config_file_name . '.php') ?: $raw;
        $this->mergeConfigFrom($path, 'api');
    }

    /**
     * Register Commands
     */
    private function registerCommands()
    {
        $this->commands([
            CheckGiftcodesCommand::class
        ]);
    }


    /**
     * Register helpers.
     */
    private function registerHelpers()
    {
        if (file_exists($helperFile = __DIR__ . '/helpers/emails.php')) {
            require_once($helperFile);
        }

        if (file_exists($helperFile = __DIR__ . '/helpers/helpers.php')) {
            require_once $helperFile;
        }
    }

    /**
     * Register Observers
     */
    private function registerObservers()
    {
        Setting::observe(SettingObserver::class);
        Giftcode::observe(GiftcodeObserver::class);
        EmailContent::observe(EmailContentObserver::class);
    }

    /**
     * Determine if we should register the migrations.
     *
     * @return bool
     */
    private function shouldMigrate()
    {
        return GiftCodeConfigure::$runsMigrations;
    }
    private function seed()
    {
        if (isset($_SERVER['argv']))
            if (array_search('db:seed', $_SERVER['argv'])) {
                GiftCodeConfigure::seed();
            }
    }

}
