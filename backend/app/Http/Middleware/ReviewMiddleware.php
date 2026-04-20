<?php

namespace App\Http\Middleware;

use Closure;

class ReviewMiddleware
{
    public function handle($request, Closure $next)
    {
        $authUser = auth()->user();

        if ($authUser && !$authUser->hasRole('reviewer')) {
            abort(403);
        }

        return $next($request);
    }
}
