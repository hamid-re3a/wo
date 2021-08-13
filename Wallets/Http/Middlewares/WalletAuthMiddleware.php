<?php

namespace Wallets\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Wallets\Models\WalletUser;

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
            $request->hasHeader('X-user-id')
        ) {
            $user = WalletUser::with('wallet')->find($request->header('X-user-id'));
            $request->merge([
                'wallet_user' => $user
            ]);
            return $next($request);
        }

        return api()->unauthorized();

    }


}
