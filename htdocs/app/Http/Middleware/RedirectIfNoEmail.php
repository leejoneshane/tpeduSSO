<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNoEmail
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $email = Auth::guard($guard)->user()->email;
            if (empty($email)) return redirect()->route('profile');
        }
        return $next($request);
    }
}