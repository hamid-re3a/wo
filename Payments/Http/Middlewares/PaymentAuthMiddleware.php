<?php

namespace Payments\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use User\Models\User;

class PaymentAuthMiddleware
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
            $request->hasHeader('X-user-member-id') &&
            $request->hasHeader('X-user-sponsor-id') &&
            $request->hasHeader('X-user-block-type') &&
            $request->hasHeader('X-user-is-freeze') &&
            $request->hasHeader('X-user-is-deactivate')
        ) {
            $user = User::query()->firstOrCreate([
                'id' => $request->header('X-user-id')
            ]);
            $user->update([
                'first_name' => $request->header('X-user-first-name'),
                'last_name' => $request->header('X-user-last-name'),
                'email' => $request->header('X-user-email'),
                'username' => $request->header('X-user-username'),
                'member_id' => $request->header('X-user-member-id'),
                'sponsor_id' => !empty($request->header('X-user-sponsor-id')) ? $request->header('X-user-sponsor-id') : null,
                'block_type' => !empty($request->header('X-user-block-type')) ? $request->header('X-user-block-type') : null,
                'is_freeze' => !empty($request->header('X-user-is-freeze')) ? $request->header('X-user-is-freeze') : null,
                'is_deactivate' => !empty($request->header('X-user-is-deactivate')) ? $request->header('X-user-is-deactivate') : null,
            ]);
            $request->merge([
                'user' => $user
            ]);
            return $next($request);
        }

        return api()->unauthorized();

    }


}
