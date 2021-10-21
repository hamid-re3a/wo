<?php

namespace Wallets;

use Wallets\Commands\ProcessBTCWithdrawalRequestsCommand;
use Wallets\Models\EmailContent;
use Wallets\Models\Setting;
use Wallets\Models\Transaction;
use Wallets\Models\Transfer;
use Wallets\Models\WithdrawProfit;
use Wallets\Observers\EmailContentObserver;
use Wallets\Observers\SettingObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Wallets\Observers\TransactionObserver;
use Wallets\Observers\TransferObserver;
use Wallets\Observers\WithdrawProfitObserver;
use Wallets\Services\KycClientFacade;
use Wallets\Services\KycGrpcClientProvider;
use Wallets\Services\MlmClientFacade;
use Wallets\Services\MlmGrpcClientProvider;

class WalletServiceProvider extends ServiceProvider
{
    private $namespace = 'Wallets';
    private $name = 'wallets';
    private $config_file_name = 'wallet-domain';

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

        $this->registerFacades();

        $this->registerObservers();

        $this->setupConfig();

        $this->registerHelpers();

        $this->registerCommands();

        $this->registerWalletsName();

        Route::prefix('v1/wallets')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->seed();
        }

        $this->publishes([
            __DIR__ . '/config/'.$this->config_file_name.'.php' => config_path($this->config_file_name . '.php'),
        ], 'wallet-config');

        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang'),
        ], 'wallet-resources');
    }

    /**
     * Register Facades
     */
    private function registerFacades()
    {
        MlmClientFacade::shouldProxyTo(MlmGrpcClientProvider::class);
        KycClientFacade::shouldProxyTo(KycGrpcClientProvider::class);
    }

    /**
     * Register Commands
     */
    private function registerCommands()
    {
        $this->commands([
            ProcessBTCWithdrawalRequestsCommand::class
        ]);
    }

    /**
     * Register Observers
     */
    private function registerObservers()
    {
        Setting::observe(SettingObserver::class);
        EmailContent::observe(EmailContentObserver::class);
        WithdrawProfit::observe(WithdrawProfitObserver::class);
        Transaction::observe(TransactionObserver::class);
        Transfer::observe(TransferObserver::class);
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
     * Register helpers.
     */
    private function registerHelpers()
    {
        if (file_exists($helperFile = __DIR__ . '/helpers/constant.php')) {
            require_once($helperFile);
        }

        if (file_exists($helperFile = __DIR__ . '/helpers/emails.php')) {
            require_once($helperFile);
        }

        if (file_exists($helperFile = __DIR__ . '/helpers/helpers.php')) {
            require_once $helperFile;
        }

        if (file_exists($helperFile = __DIR__ . '/helpers/queryMacros.php')) {
            require_once $helperFile;
        }
    }

    /**
     * Register wallets name
     */
    private function registerWalletsName()
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
    private function shouldMigrate()
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
