<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Providers\LdapServiceProvider;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    public function username()
    {
	return 'username';
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

		$openldap = new LdapServiceProvider();
		$username = $request->get('username');
		$password = $request->get('password');
		if (substr($username,0,3) == 'dc=') {
	    	if (!$openldap->checkSchoolAdmin($username))
				return redirect()->back()->with("error","學校代號不存在！");
	    	if ($openldap->schoolLogin($username, $password)) {
				$dc = substr($username,3);
				$request->session()->put('dc', $dc);
				return redirect()->route('schoolAdmin');
	    	} else {
				return redirect()->back()->with("error","學校管理密碼不正確！");
	    	}
		}

        $idno = $openldap->checkAccount($username);
        if (!$idno) return redirect()->back()->with("error","查無此使用者帳號！");
        $status = $openldap->checkStatus($idno);
        if ($status == 'inactive') return redirect()->back()->with("error","很抱歉，您已經被管理員停權！");
        if ($status == 'deleted') return redirect()->back()->with("error","很抱歉，您已經被管理員刪除！");

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
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
}
