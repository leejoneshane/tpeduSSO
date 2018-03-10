<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateSchoolAdmin
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest() || !Auth::guard($guard)->user()->ldap['is_schoolAdmin']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/');
            }
        }
        return $next($request);
    }
}