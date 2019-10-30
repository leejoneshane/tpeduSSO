<?php

namespace App\Http\Controllers\Auth;
use DB;
use Config;
use Log;
use App\User;
use App\OauthSocialiteAccount;
use App\Rules\idno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Auth\SamlAuth;
use App\Providers\LdapServiceProvider;
use Socialite;

trait AuthenticatesUsers
{
    use RedirectsUsers, ThrottlesLogins, SamlAuth;

    protected $maxAttempts = 5;
    protected $decayMinutes = 60;

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
        $openldap = new LdapServiceProvider();
        if(!isset($request['SAMLRequest'])) $this->validateLogin($request);

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

        //Scan QRcode 
        if($request->session()->has('qrcodeObject')) {
            return app('App\Http\Controllers\HomeController')->connectChildQrcode($request);
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
            'captcha' => 'required|captcha'
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
        $idno = $request->get('idno');
        $openldap = new LdapServiceProvider();
        $entry = $openldap->getUserEntry($idno);
        $data = $openldap->getUserData($entry, 'mail');
        if (!isset($data['mail']) || empty($data['mail'])) return redirect()->route('profile');
        
        if (Auth::check() && isset($request['SAMLRequest'])) {
            $this->handleSamlLoginRequest($request);
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

    public function oauth_google()
    {
        return Socialite::with('Google')->stateless()->redirect();
    }

    public function oauth_google_callback(Request $request)
    {
        try {
            $socialite = Socialite::driver('Google')->stateless()->user();
            return $this->oauth_callback($request,'Google',$socialite);
        } catch (\Exception $e){
            Log::debug('Oauth Cancel submit or unknow event:'.$e->getMessage());
        }
        return redirect()->route('login');
    }

    public function oauth_facebook()
    {
        return Socialite::with('facebook')->stateless()->redirect();
    }

    public function oauth_facebook_callback(Request $request)
    {
        try {
            $socialite = Socialite::driver('facebook')->stateless()->user();
            return $this->oauth_callback($request,'facebook',$socialite);
        } catch (\Exception $e){
            Log::debug('Oauth Cancel submit or unknow event:'.$e->getMessage());
        }
        return redirect()->route('login');
    }

    public function oauth_yahoo()
    {
        return Socialite::driver('yahoo')->redirect();
    }

    public function oauth_yahoo_callback(Request $request)
    {
        try {
            $socialite = Socialite::driver('yahoo')->stateless()->user();
            return $this->oauth_callback($request,'yahoo',$socialite);
        } catch (\Exception $e){
            Log::debug('Oauth Cancel submit or unknow event:'.$e->getMessage());
        }
        return redirect()->route('login');

    }

    public function oauth_callback(Request $request,$source,$socialite) 
    {

        $openldap = new LdapServiceProvider();
        //先確認該ID 是否有在DB(綁定)之中
        $data = DB::table('oauth_socialite_account')->where('oauth_id',$socialite->id)->where('source',$source)->first();
        if($data) {
            //防呆查詢是否LDAP有無此使用者 
            if ($openldap->checkIdno($data->idno)==false)
                return redirect()->back()->with("error","該社群帳號綁定的使用者已不存在，請洽系統管理人員！")->withInput();
            // 有在DB(綁定)之中就Login認證通過...
            $entry = $openldap->getUserEntry($data->idno);
            $user = new \App\User();
            if ($entry) {
                $dataLdap = $openldap->getUserData($entry);
                $user->idno = $dataLdap['cn'];
                if (isset($dataLdap['uid'])) {
                    if (is_array($dataLdap['uid'])) {
                        $user->uname = $dataLdap['uid'][0];
                    } else {
                        $user->uname = $dataLdap['uid'];
                    }
                }
                $user->name = $dataLdap['displayName'];
                $user->uuid = $dataLdap['entryUUID'];
                $user->email = $socialite->email;
                $user->password = $user->idno."password"; //\Hash::make($user->idno."password");

                Auth::login($user);
                if (Auth::attempt(['username' => 'cn='.$user->idno,'password' => $user->idno."password"])) {
                    //導至首頁
                    if(Auth::check()) {
                        //Scan QRcode 
                        if($request->session()->has('qrcodeObject')) {
                            return app('App\Http\Controllers\HomeController')->connectChildQrcode($request);
                        } else {
                            return redirect()->intended($this->redirectPath());
                        }
                    } else {
                        return redirect()->route('login')->with('error','社群帳號登入失敗，請洽系統管理人員!');  
                    } 
                } else {
                    return redirect()->route('login')->with('error','社群帳號登入驗證失敗，請洽系統管理人員!'); 
                }
            } else {
                //不可能發生 防呆  
                $error="noRole";
                return redirect()->route('login')->with('error','社群帳號與LDAP驗證失敗，請洽系統管理人員!'); 
            }  
        } else {
            $request->session()->put('socialite_cache', $socialite);
            $request->session()->put('source_cache', $source);
            return redirect()->route('registerThird');
        }     
    }

    public function registerThird(Request $request)
    {
        $attributes = [
            'idno' => '身分字號',
            'displayName' => '姓名',
            'email' => 'eMail信箱',
            'mobile' => '電話號碼',
        ];

        $this->validate($request, [
            'idno' =>  ['required', new idno()],
            'displayName' => 'required|string',
            'email' => 'required|email',
            'mobile' => 'nullable|digits:10|numeric',
        ],[],$attributes);
          
        $openldap = new LdapServiceProvider();

        //先去核對目前LDAP 無此使用者帳號 (cn=account) 才能建立 
        if ($openldap->checkIdno($request->idno)==false) {
            //檢查Email 手機 有無重覆 (目前Users 有限唯一 因為有功能可以利用它們當帳號)
            if(!empty($request->email)) {
              $checkEmail = DB::table('users')->where('email',$request->email)->first();
              if($checkEmail) {
                return redirect()->back()->with("error","該eMail信箱已於本系統有帳號使用或已綁定其它社群帳號，請修改您的Email，謝謝！")->withInput();
              }
            } 
            if(!empty($request->mobile)) {
                $checkMobile = DB::table('users')->where('mobile',$request->mobile)->first();
                if($checkEmail) {
                  return redirect()->back()->with("error","該電話號碼箱已於本系統有帳號使用或已綁定其它社群帳號，請修改您的電話號碼，謝謝！")->withInput();
                }
              }          
          //無-建立LDAP 使用者 (cn=people)
            $info = array();
            $info['dn'] = "cn=$request->idno,".Config::get('ldap.userdn');
            $info['objectClass'] = array('tpeduPerson', 'inetUser');
            $info['cn'] = $request->idno;
            $info["uid"] = $request->email;
            $info["userPassword"] =$request->idno."password";
            $info['o'] = '';
            $info['ou'] = '';
            $info['title'] = '';
            $info['info'] = '';
            $info['inetUserStatus'] = 'active';
            $info['employeeType'] = '家長';
            $name = $this->guess_name($request->displayName);
            $info['sn'] = $name[0];
            $info['givenName'] = $name[1];
            $info['displayName'] = $request->displayName;
            $info['mail'] =$request->email;
            $result = $openldap->createEntry($info);

            $data = DB::table('oauth_socialite_account')->where('oauth_id',$request->id)->where('source',$request->sourceFrom)->first();
 
            if(!$data) {
                //建立DB oauth_socialite_account 使用者
                $oauthUser = new \App\OauthSocialiteAccount();
                $oauthUser->idno=$request->idno;
                $oauthUser->oauth_id=$request->id;
                $oauthUser->email=$request->email;
                $oauthUser->source=$request->sourceFrom;
                $oauthUser->save();
            } 
            //進行自動登入
                $entry = $openldap->getUserEntry($request->idno);
                $user = new \App\User();
                    if ($entry) {
                        $data = $openldap->getUserData($entry);
                        $user->idno = $request->idno;
                        if (isset($data['uid'])) {
                            if (is_array($data['uid'])) {
                                $user->uname = $data['uid'][0];
                            } else {
                                $user->uname = $data['uid'];
                            }
                        }
                        $user->name = $data['displayName'];
                        $user->uuid = $data['entryUUID'];
                    } else {
                        //不可能會進到這裡才對 防呆
                        $user->name = $request->displayName;
                        $user->uuid = $request->idno;
                    }  
            
                //登入
                Auth::login($user);
                if (Auth::attempt(['username' => 'cn='.$request->idno,'password' => $request->idno."password"])) {
                    //導至首頁
                    if(Auth::check()) {
                        //Scan QRcode 
                        if($request->session()->has('qrcodeObject')) {
                            return app('App\Http\Controllers\HomeController')->connectChildQrcode($request);
                        } else {
                            return redirect()->intended($this->redirectPath());
                        }
                    } else {
                        return redirect()->route('login')->with('error','社群帳號無法登入');  
                    } 
                } else {
                    return redirect()->route('login')->with('error','社群帳號驗證失敗'); 
                }
        
        
            } else {   //有-Back Error (有帳號不允許建立)  
          return redirect()->back()->with("error","該身分證字號已於本系統有帳號使用或已綁定其它社群帳號，請利用右上方的登入使用其它帳號登入，謝謝！")->withInput();
        }
    }

    public function showRegisterThirdForm(Request $request)
    {
        $so2=$request->session()->get('socialite_cache');
        $source2=$request->session()->get('source_cache');
        return view('auth.registerthird', [ 'source'=>$source2,'socialite' => $so2 ]);
    }
    
    function guess_name($myname) {
		$len = mb_strlen($myname, "UTF-8");
		if ($len > 3) {
			return array(mb_substr($myname, 0, 2, "UTF-8"), mb_substr($myname, 2, null, "UTF-8"));
		} else {
			return array(mb_substr($myname, 0, 1, "UTF-8"), mb_substr($myname, 1, null, "UTF-8"));
		}
	}	
    
}
