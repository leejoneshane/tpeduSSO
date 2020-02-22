<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateTutor
{
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::user();
        if (Auth::guard($guard)->guest() || !isset($user->ldap['tpTutorClass'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/');
            }
        }
        return $next($request);
    }
}