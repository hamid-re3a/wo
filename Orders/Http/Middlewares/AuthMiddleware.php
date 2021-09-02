<?php

namespace Orders\Http\Middlewares;

use App\Jobs\User\UserGetDataJob;
use Closure;
use Illuminate\Http\Request;
use User\Models\User;
use User\Services\UserUpdate;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $user_update = new UserUpdate();
        $user_update->setId($request->header('X-user-id'));
        $user_update->setQueueName('subscriptions');

        if ($request->hasHeader('X-user-id') && $request->hasHeader('X-user-hash')) {
            $user_hash_request = $request->header('X-user-id');
            $user = User::whereId($request->header('X-user-hash'))->first();
            $hash_user_service = \Illuminate\Support\Facades\Hash::make(serialize($user->getUserService()));

            /**
             * if there is not exist user. get data user complete from api gateway
             * error code 5000 is for data user not exist log for development
             */
            if ($user === null) {
                UserGetDataJob::dispatch($user_update);
                return  api()->error('please try another time!',null,5000);
            }

            /**
             * if there is not update data user. get data user complete from api gateway
             * error code 5001 is for data user not update log for development
             */
            if (!\Illuminate\Support\Facades\Hash::check($user_hash_request,$hash_user_service)){
                UserGetDataJob::dispatch($user_update);
                return  api()->error('please try another time!',null,5001);
            }

            $request->merge([
                'user' => $user
            ]);
            return $next($request);
        }

        return api()->unauthorized();
    }


}
