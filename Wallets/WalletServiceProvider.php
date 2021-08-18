<?php

namespace Wallets;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Wallets\Http\Middlewares\WalletAuthMiddleware;

class WalletServiceProvider extends ServiceProvider
{
    private $namespace = 'Wallets';
    private $name = 'wallets';
    private $config_file_name = 'wallet';

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
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], $this->name . '-migrations');

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

        $this->registerMiddlewares();

        $this->registerWalletsName();

        Route::prefix('v1/wallets')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->seed();

            $this->publishes([
                __DIR__ . '/config/'.$this->config_file_name.'.php' => config_path($this->config_file_name . '.php'),
            ], 'api-response');
        }
    }


    /**
     * Set Config files.
     */
    protected function setupConfig()
    {
        $path = realpath($raw = __DIR__ . '/config/' . $this->config_file_name . '.php') ?: $raw;
        $this->mergeConfigFrom($path, 'api');
    }


    /**
     * Register helpers.
     */
    protected function registerHelpers()
    {
        if (file_exists($helperFile = __DIR__ . '/helpers/helpers.php')) {
            require_once $helperFile;
        }
    }

    /**
     * Register Middlewares
     */
    protected function registerMiddlewares()
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddlewareToGroup('api',WalletAuthMiddleware::class);
    }

    /**
     * Register wallets name
     */
    protected function registerWalletsName()
    {
        config([
            'depositWallet' => 'Deposit Wallet',
            'earningWallet' => 'Earning Wallet'
        ]);
    }


    /**
     * Determine if we should register the migrations.
     *
     * @return bool
     */
    protected function shouldMigrate()
    {
        return WalletConfigure::$runsMigrations;
    }
    private function seed()
    {
        if (isset($_SERVER['argv']))
            if (array_search('db:seed', $_SERVER['argv'])) {
                WalletConfigure::seed();
            }
    }

}
