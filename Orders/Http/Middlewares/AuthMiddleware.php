<?php

namespace Orders\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Orders\Models\OrderUser;

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
        if (
            $request->hasHeader('X-user-id')
        ) {
            $user = OrderUser::query()->firstOrCreate([
                'id' => $request->header('X-user-id')
            ]);

            $request->merge([
                'order_user' => $user
            ]);
            return $next($request);
        }

        return api()->unauthorized();

    }


}
