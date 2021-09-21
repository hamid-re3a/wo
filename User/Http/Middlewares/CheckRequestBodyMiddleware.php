<?php

namespace User\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;

class CheckRequestBodyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->has('member_id'))
            updateUserFromGrpcServerByMemberId($request->get('member_id'));

        if($request->has('user_id'))
            updateUserFromGrpcServer($request->get('user_id'));

        return $next($request);
    }

}
