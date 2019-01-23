<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNoEmail
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
        	$user = Auth::guard($guard)->user();
            if (empty($user->email)) return redirect()->route('profile');
            if (!isset($user->ldap['uid']) || substr($user->uname,-9) == $user->indo) return redirect()->route('changeAccount');
        }
        return $next($request);
    }
}