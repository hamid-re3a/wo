<?php

namespace Payments;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Payments\Services\Processors\PaymentFacade;
use Payments\Services\Processors\PaymentProcessor;
use Payments\Services\Processors\PayoutFacade;
use Payments\Services\Processors\PayoutProcessor;

class PaymentServiceProvider extends ServiceProvider
{
    private $routes_namespace = 'Payments\Http\Controllers';
    private $namespace = 'Payments';
    private $name = 'payments';
    private $config_file_name = 'payment';

    /**
     * Register API class.
     *
     * @return void
     */
    public function register()
    {

        /**
         * related Facades
         */
        PaymentFacade::shouldProxyTo(PaymentProcessor::class);
        PayoutFacade::shouldProxyTo(PayoutProcessor::class);

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

        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang'),
        ], 'payment-resources');

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

        Route::prefix('v1/payments')
            ->middleware('api')
            ->namespace($this->routes_namespace)
            ->group(__DIR__ . '/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->seed();

            $this->publishes([
                __DIR__ . '/config/' . $this->config_file_name . '.php' => config_path($this->config_file_name . '.php'),
            ], 'payment-config');
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
     * Determine if we should register the migrations.
     *
     * @return bool
     */
    protected function shouldMigrate()
    {
        return PaymentConfigure::$runsMigrations;
    }

    private function seed()
    {
        if (isset($_SERVER['argv']))
            if (array_search('db:seed', $_SERVER['argv'])) {
                PaymentConfigure::seed();
            }
    }

}
