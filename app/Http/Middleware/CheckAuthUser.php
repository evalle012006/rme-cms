<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAuthUser
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()) {
            return redirect('/');
        } else if (!Auth::user()->email_verified_at) {
            // logout user if is not verified
            Auth::logout();
            return redirect('/');
        }
        return $next($request);
    }
}
