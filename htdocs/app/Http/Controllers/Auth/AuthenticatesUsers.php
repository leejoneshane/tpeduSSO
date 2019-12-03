<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Auth\SamlAuth;
use App\Providers\LdapServiceProvider;

trait AuthenticatesUsers
{
    use RedirectsUsers, ThrottlesLogins, SamlAuth;

    protected $maxAttempts = 5;
    protected $decayMinutes = 15;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $openldap = new LdapServiceProvider();
		$username = $request->get('username');
		$password = $request->get('password');
		if (substr($username,0,3) == 'dc=') {
	    	if (!$openldap->checkSchoolAdmin($username))
				return back()->with("error","學校代號不存在！");
	    	if ($openldap->schoolLogin($username, $password)) {
				$dc = substr($username,3);
				$request->session()->put('dc', $dc);
				return redirect()->route('schoolAdmin');
	    	} else {
				return back()->with("error","學校管理密碼不正確！");
	    	}
		}

        if (substr($username,0,3) == 'cn=') {
            $idno = $openldap->checkIdno($username);
        } else {
            $idno = $openldap->checkAccount($username);
        }
        if (!$idno) return back()->with("error","查無此使用者帳號！");
	    $status = $openldap->checkStatus($idno);
	    if ($status == 'inactive') return back()->with("error","很抱歉，您已經被管理員停權！");
        if ($status == 'deleted') return back()->with("error","很抱歉，您已經被管理員刪除！");
        if (substr($username,-9) == substr($idno, -9)) {
            if ($openldap->authenticate($username,$password)) {
                $request->session()->put('idno', $idno);
                if ($password == substr($idno, -6)) $request->session()->put('mustChangePW', true);
                return redirect()->route('changeAccount');
            }
        }
        if ($password == substr($idno, -6)) {
            if ($openldap->authenticate($username,$password)) {
                $request->session()->put('idno', $idno);
                return redirect()->route('changePassword');
            }
        }
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $request['idno'] = $idno;
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if (Auth::check()) {
            if (isset($request['SAMLRequest'])) {
                if ($user->nameID()) {
                    $this->handleSamlLoginRequest($request);
                } else {
                    return redirect('/')->with('status', '很抱歉，您的帳號尚未同步到 G-Suite，請稍候再登入 G-Suite 服務！');
                }
            }
//            if (!isset($user->email) || empty($user->email)) return redirect()->route('profile');
        }     
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect('/');
    }
    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
