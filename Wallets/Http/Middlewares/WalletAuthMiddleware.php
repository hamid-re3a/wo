<?php

namespace Wallets\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use User\Models\User;

class WalletAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->hasHeader('X-user-id') &&
            $request->hasHeader('X-user-first-name') &&
            $request->hasHeader('X-user-last-name') &&
            $request->hasHeader('X-user-email') &&
            $request->hasHeader('X-user-username')
        ) {
            $user = User::query()->firstOrCreate([
                'id' => $request->header('X-user-id')
            ]);
            $user->update([
                'first_name' => $request->header('X-user-first-name'),
                'last_name' => $request->header('X-user-last-name'),
                'email' => $request->header('X-user-email'),
                'username' => $request->header('X-user-username'),
            ]);
            $request->merge([
                'wallet_user' => $user
            ]);
            return $next($request);
        }

        return api()->unauthorized();

    }


}
