<?php

namespace Giftcode\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Giftcode\Models\GiftcodeUser;

class GiftcodeAuthMiddleware
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
            $user = GiftcodeUser::with('giftcodes')->find($request->header('X-user-id'));
            $request->merge([
                'giftcode_user' => $user
            ]);
            return $next($request);
        }

        return api()->unauthorized();

    }


}
