<?php

namespace App\Http\Controllers\Auth;

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

    public function redirect($provider)
    {
        return Socialite::with($provider)->redirect();
    }

    public function handleCallback(Request $request, $provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
            $userID = $user->getId();
            if (Auth::check()) {
                $myuser = Auth::user();
                SocialiteAccount::create([
                    'idno' => $myuser->idno,
                    'socialite' => $provider,
                    'userID' => $userID,
                ]);
                return redirect()->route('socialite')->with('success', "$provider 社群帳號： $userID 綁定完成！");
            } else {
                $account = SocialiteAccount::where('userID', $userID)->where('socialite', $provider)->first();
                if($account) {
                    $myuser = $account->user()->first();
                    if ($myuser) {
                        Auth::login($myuser);
                        return redirect('/');
                    } 
                }
                return redirect()->route('login')->with('error', '這個社群帳號尚未綁定使用者，所以無法登入！');
            }
        } catch (\Exception $e){
            return redirect()->route('login')->with('error', "使用 $provider 帳號登入失敗 $e->getMessage()");
        }
    }

}
