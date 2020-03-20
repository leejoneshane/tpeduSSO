<?php

namespace App\Http\Controllers\Auth;

use Log;
use Auth;
use Socialite;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\SocialiteAccount;
use App\User;

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

    public function redirectToGoogle()
    {
        return Socialite::with('Google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $user = Socialite::driver('Google')->user();
            return $this->SocialiteLogin($request, 'Google', $user);
        } catch (\Exception $e){
            Log::debug('使用 Google 帳號登入失敗：'.$e->getMessage());
            return redirect()->route('login');
        }
    }

    public function redirectToFacebook()
    {
        return Socialite::with('facebook')->redirect();
    }

    public function handleFacebookCallback(Request $request)
    {
        try {
            $user = Socialite::driver('facebook')->user();
            return $this->SocialiteLogin($request, 'Facebook', $user);
        } catch (\Exception $e){
            Log::debug('使用 Facebook 帳號登入失敗：'.$e->getMessage());
            return redirect()->route('login');
        }
    }

    public function redirectToYahoo()
    {
        return Socialite::with('Yahoo')->redirect();
    }

    public function handleYahooCallback(Request $request)
    {
        try {
            $user = Socialite::driver('Yahoo')->user();
            return $this->SocialiteLogin($request, 'Yahoo', $user);
        } catch (\Exception $e){
            Log::debug('使用 Yahoo 帳號登入失敗：'.$e->getMessage());
            return redirect()->route('login');
        }
    }

    public function redirectToLine()
    {
        return Socialite::with('line')->redirect();
    }

    public function handleLineCallback(Request $request)
    {
        try {
            $user = Socialite::driver('line')->user();
            return $this->SocialiteLogin($request, 'Line', $user);
        } catch (\Exception $e){
            Log::debug('使用 Line 帳號登入失敗：'.$e->getMessage());
            return redirect()->route('login');
        }
    }

    public function SocialiteLogin(Request $request, $socialite, $user) 
    {
        $userID = $user->getId();
		if (Auth::check()) {
            $myuser = Auth::user();
            SocialiteAccount::create([
                'idno' => $myuser->idno,
                'socialite' => $socialite,
                'userID' => $userID,
            ]);
            return redirect()->route('socialite')->with('success',$socialite.'社群帳號：'.$userID.'綁定完成！');
		} else {
            $account = SocialiteAccount::where('userID', $userID)->where('socialite', $socialite)->first();
            if($account) {
                $myuser = $account->user();
                if ($myuser) {
                    Auth::login($myuser);
                    return redirect()->intended($this->redirectTo);
                } 
            }
            return redirect()->route('login')->with('error','這個社群帳號尚未綁定使用者，所以無法登入！');
        }
    }

}
