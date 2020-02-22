<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateParent
{
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::user();
        $role = '';
        if (isset($user->ldap['employeeType'])) $role = $user->ldap['employeeType'];
        if (Auth::guard($guard)->guest() || $role == '學生') {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/');
            }
        }
        return $next($request);
    }
}