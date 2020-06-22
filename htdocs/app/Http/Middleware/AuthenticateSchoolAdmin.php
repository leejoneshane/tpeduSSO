<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateSchoolAdmin
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/');
            }
        }
        $user = Auth::guard($guard)->user();
        $dc = $request->route('dc');
        if ($user->is_parent) {
            $valid = false;
        } else {
            $valid = false;
            if (is_array($user->ldap['adminSchools'])) {
                if (in_array($dc, $user->ldap['adminSchools'])) {
                    $valid = true;
                }
            } else {
                if ($dc == $user->ldap['adminSchools']) {
                    $valid = true;
                }
            }
        }
        if (!$valid) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
