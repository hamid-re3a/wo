<?php

namespace Orders;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Orders\Services\MlmClientFacade;
use Orders\Services\MlmGrpcClientProvider;

class OrderServiceProvider extends ServiceProvider
{
    private $routes_namespace = 'Orders\Http\Controllers';
    private $namespace = 'Orders';
    private $name = 'orders';
    private $config_file_name = 'order';

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

        MlmClientFacade::shouldProxyTo(MlmGrpcClientProvider::class);

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

        Route::prefix('v1/orders')
            ->middleware('api')
            ->namespace($this->routes_namespace)
            ->group(__DIR__ . '/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->seed();

            $this->publishes([
                __DIR__ . '/config/'.$this->config_file_name.'.php' => config_path($this->config_file_name . '.php'),
            ], 'api-response');

            $this->publishes([
                __DIR__ . '/resources/lang' => resource_path('lang'),
            ], 'user-resources');
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
        return OrderConfigure::$runsMigrations;
    }
    private function seed()
    {
        if (isset($_SERVER['argv']))
            if (array_search('db:seed', $_SERVER['argv'])) {
                OrderConfigure::seed();
            }
    }

}
