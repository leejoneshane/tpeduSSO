<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Config;
use Notification;
use Illuminate\Http\Request;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;
use App\Notifications\AccountChangeNotification;
use App\Notifications\PasswordChangeNotification;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }
    
    public function showProfileForm()
    {
		return view('auth.profile', [ 'user' => Auth::user() ]);
    }

    public function changeProfile(Request $request)
    {
		$openldap = new LdapServiceProvider();
		$email = $request->get('email');
		$mobile = $request->get('mobile');
		$user = Auth::user();
		$idno = $user->idno;
		$accounts = $openldap->getUserAccounts($idno);
		$userinfo = array();
		if ($email != $user->email) {
	    	$validatedData = $request->validate([
			    'email' => 'required|email|unique:users',
			]);
	    	if (!$openldap->emailAvailable($idno, $email))
				return back()->withInput()->with("error","您輸入的電子郵件已經被別人使用，請您重新輸入一次！");
	    	$userinfo['mail'] = $email;
	    	$user->email = $email;
		}
		if ($mobile && $mobile != $user->mobile) {
	    	$validatedData = $request->validate([
			    'mobile' => 'nullable|string|digits:10|numeric',
			]);
			if (!$openldap->mobileAvailable($idno, $mobile))
				return back()->withInput()->with("error","您輸入的手機號碼已經被別人使用，請您重新輸入一次！");
    		$userinfo['mobile'] = $mobile;
    		$user->mobile = $mobile;
		}
		if (!$mobile) {
    		$userinfo['mobile'] = array();
    		$user->mobile = null;
		}
		$user->save();
		$entry = $openldap->getUserEntry($idno);
		$result = $openldap->updateData($entry, $userinfo);
		if (!$result) return back()->withInput()->with("error", "無法變更人員資訊！".$openldap->error());
		if ($request->get('login-by-email', 'no') == "yes" && !empty($email)) $accounts[] = $email;
		if ($request->get('login-by-mobile', 'no') == "yes" && !empty($mobile)) $accounts[] = $mobile;
		$accounts = array_values(array_unique($accounts));
		$openldap->updateData($entry, array( 'uid' => $accounts));
		$openldap->updateAccounts($entry, $accounts);
		return back()->withInput()->with("success","您的個人資料設定已經儲存！");
    }

    public function showChangeAccountForm()
    {
		return view('auth.changeaccount');
    }

    public function changeAccount(Request $request)
    {
		if (Auth::check()) {
			$user = Auth::user();
			$idno = $user->idno;
		} else {
		  $idno = $request->session()->pull('idno');
		}
		$validatedData = $request->validate([
			'new-account' => 'required|alpha_num|min:6|confirmed',
		]);
		$new = $request->get('new-account');
		$openldap = new LdapServiceProvider();
		$accounts = $openldap->getUserAccounts($idno);
		$match = false;
		foreach ($accounts as $account) {
    		if ($new == $account) $match = true;
		}
		if ($match) return back()->withInput()->with("error","新帳號不可以跟舊的帳號相同，請重新想一個新帳號再試一次！");
		if(strcmp($idno, $new) == 0) return back()->withInput()->with("error","新帳號不可以跟身分證字號相同，請重新想一個新帳號再試一次！");
		if (!$openldap->accountAvailable($new)) return back()->withInput()->with("error","您輸入的帳號已經被別人使用，請您重新輸入一次！");
		$entry = $openldap->getUserEntry($idno);
		$data = $openldap->getUserData($entry);
		if (empty($accounts)) {
			$openldap->addAccount($entry, $new, "自建帳號");
			if (Auth::check()) {
				$user->uname = $new;
				$user->save();
				if (!empty($user->email)) $user->notify(new PasswordChangeNotification($new));
			} else {
				$user = User::where('idno', $idno)->first();
				if ($user) {
					$user->uname = $new;
					$user->save();
				}
				if (isset($data['mail'])) Notification::route('mail', $data['mail'])->notify(new AccountChangeNotification($new));
			}
			return back()->withInput()->with("success","帳號建立成功！");
		} else {
			$openldap->renameAccount($entry, $new);
			if (Auth::check()) {
				$user->uname = $new;
				$user->save();
				if (!empty($user->email)) $user->notify(new PasswordChangeNotification($new));
			} else {
				$user = User::where('idno', $idno)->first();
				if ($user) {
					$user->uname = $new;
					$user->save();
				}
				if (isset($data['mail'])) Notification::route('mail', $data['mail'])->notify(new AccountChangeNotification($new));
			}
			return redirect('login')->with("success","帳號變更成功，請重新登入！");
		}
    }

    public function showChangePasswordForm()
    {
		return view('auth.changepassword');
    }

    public function changePassword(Request $request)
    {
		if (Auth::check()) {
			$user = Auth::user();
			$idno = $user->idno;
		} else {
		    $idno = $request->session()->pull('idno');
		}
		$new = $request->get('new-password');
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($idno);
		$data = $openldap->getUserData($entry);
		if ($openldap->userLogin("cn=$idno", $new))
	    	return back()->withInput()->with("error","新密碼不可以跟舊的密碼相同，請重新想一個新密碼再試一次！");
		$validatedData = $request->validate([
			'new-password' => 'required|string|min:6|confirmed',
		]);
		if (Auth::check()) {
			$user->resetLdapPassword($new);
			$user->password = \Hash::make($new);
			$user->save();
			if (!empty($user->email)) $user->notify(new PasswordChangeNotification($new));
		} else {
			$openldap->resetPassword($entry, $new);
			$user = User::where('idno', $idno)->first();
			if ($user) {
				$user->password = \Hash::make($new);
				$user->save();
			}
			if (isset($data['mail'])) Notification::route('mail', $data['mail'])->notify(new PasswordChangeNotification($new));
		}
		return redirect('login')->with("success","密碼變更成功，請重新登入！");
    }

}
