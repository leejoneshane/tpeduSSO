<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateParent
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->route('login');
            }
        }
        $user = Auth::guard($guard)->user();
        if ($user->is_parent) return $next($request);
        if (isset($user->ldap['employeeType']) && $user->ldap['employeeType'] != '學生') return $next($request);
        if ($request->ajax() || $request->wantsJson()) {
            return response('Unauthorized.', 401);
        } else {
            return redirect('/');
        }
    }
}