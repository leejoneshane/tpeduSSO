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
                if($account) {
                    $myuser = $account->user;
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

    public function socialite(Request $request)
    {
        $user = Auth::user();
        $query = $user->socialite_accounts();
        $google = $query->where('socialite', 'google')->first();
        $facebook = $query->where('socialite', 'facebook')->first();
        $yahoo = $query->where('socialite', 'yahoo')->first();
        $line = $query->where('socialite', 'line')->first();
        return view('auth.socialiteManager', [ 'google' => $google, 'facebook' => $facebook, 'yahoo' => $yahoo, 'line' => $line ]);
    }

    public function removeSocialite(Request $request)
    {
        $user = Auth::user();
        $query = $user->socialite_accounts();
        $socialite = $request->get('socialite');
        $userid = $request->get('userid');
        $account = $query->where('socialite', $socialite)->where('userID', $userid)->delete();
        return redirect()->route('socialite');
    }

}
