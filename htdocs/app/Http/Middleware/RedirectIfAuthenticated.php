<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SamlAuth;

class RedirectIfAuthenticated
{
    use SamlAuth;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if (isset($request['SAMLRequest'])) {
                if (Auth::user()->nameID()) {  
                    $this->handleSamlLoginRequest($request);
                } else {
                    return redirect()->route('home')->with('status', '很抱歉，您的帳號尚未同步到 G-Suite，請稍候再登入 G-Suite 服務！');
                }
            }
            return redirect()->route('home');
        }

        return $next($request);
    }
}
