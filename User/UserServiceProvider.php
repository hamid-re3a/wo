<?php

namespace User;

use App\Jobs\User\UserGetDataJob;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use User\Models\User;
use User\Services\Grpc\UserUpdate;

class UserServiceProvider extends ServiceProvider
{
    private $routes_namespace = 'User\Http\Controllers';
    private $namespace = 'User';
    private $name = 'User';
    private $config_file_name = 'user';

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
        Auth::viaRequest('r2f-sub-service', function (Request $request) {

            if (
                $request->hasHeader('X-user-id')
                && $request->hasHeader('X-user-hash')
                && is_numeric($request->header('X-user-id'))
            ) {


                $user_update = new UserUpdate();
                $user_update->setId($request->header('X-user-id'));
                $user_update->setQueueName('subscriptions');


                $user_hash_request = $request->header('X-user-hash');
                $user = User::query()->whereId($request->header('X-user-id'))->first();


//                /**
//                 * if there is not exist user. get data user complete from api gateway
//                 * error code 470 is for data user not exist log for development
//                 */
//                if ($user === null) {
//                    $service_user = updateUserFromGrpcServer($request);
//                    if ($service_user === null)
//                        throw new Exception('please try another time!', 470);
//                    $user = $service_user;
//                }
//
//                $hash_user_service = md5(serialize($user->getUserService()));
//
//                /**
//                 * if there is not update data user. get data user complete from api gateway
//                 * error code 471 is for data user not update log for development
//                 */
//                if ($hash_user_service != $user_hash_request) {
//                    $service_user = updateUserFromGrpcServer($request);
//                    $hash_user_service = md5(serialize($service_user));
//                    if ($hash_user_service != $user_hash_request) {
//                        UserGetDataJob::dispatch($user_update);
//                        throw new Exception('please try another time!', 471);
//                    }
//                }

                return $user;
            }

        });



        $this->setupConfig();

        $this->registerHelpers();

        Route::prefix('v1/user')
            ->middleware('api')
            ->namespace($this->routes_namespace)
            ->group(__DIR__ . '/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->seed();

            $this->publishes([
                __DIR__ . '/config/' . $this->config_file_name . '.php' => config_path($this->config_file_name . '.php'),
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

        if (file_exists($helperFile = __DIR__ . '/Helpers/constants.php')) {
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
        return UserConfigure::$runsMigrations;
    }

    private function seed()
    {
        if (isset($_SERVER['argv']))
            if (array_search('db:seed', $_SERVER['argv'])) {
                UserConfigure::seed();
            }
    }

}
