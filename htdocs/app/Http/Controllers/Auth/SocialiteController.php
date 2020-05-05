<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SocialiteAccount;

class SocialiteController extends Controller
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
                $account = SocialiteAccount::where('userId', $userID)->where('socialite', $provider)->first();
                if ($account) {
                    $myuser = $account->user;
                    if ($myuser) {
                        Auth::login($myuser);

                        return redirect('/');
                    }
                }

                return redirect()->route('login')->with('error', '這個社群帳號尚未綁定使用者，所以無法登入！');
            }
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', "使用 $provider 帳號登入失敗");
        }
    }

    public function socialite(Request $request)
    {
        $user = Auth::user();
        $google = false;
        $facebook = false;
        $yahoo = false;
        $line = false;
        $accounts = $user->socialite_accounts;
        foreach ($accounts as $a) {
            if ($a->socialite == 'google') {
                $google = $a;
            }
            if ($a->socialite == 'facebook') {
                $facebook = $a;
            }
            if ($a->socialite == 'yahoo') {
                $yahoo = $a;
            }
            if ($a->socialite == 'line') {
                $line = $a;
            }
        }

        return view('auth.socialiteManager', ['google' => $google, 'facebook' => $facebook, 'yahoo' => $yahoo, 'line' => $line]);
    }

    public function removeSocialite(Request $request)
    {
        $user = Auth::user();
        $socialite = $request->get('socialite');
        $userid = $request->get('userid');
        $account = SocialiteAccount::where('idno', $user->idno)->where('socialite', $socialite)->where('userId', $userid)->delete();

        return redirect()->route('socialite');
    }
}
