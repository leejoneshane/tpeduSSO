<?php

namespace App\Http\Controllers;

use Auth;
use Config;
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
		if ($mobile != $user->mobile) {
	    	$validatedData = $request->validate([
			    'mobile' => 'string|digits:10|numeric',
			]);
	    	if (!$openldap->mobileAvailable($idno, $mobile))
				return back()->withInput()->with("error","您輸入的手機號碼已經被別人使用，請您重新輸入一次！");
	    	$userinfo['mobile'] = $mobile;
	    	$user->mobile = $mobile;
		}
		$user->save();
		$entry = $openldap->getUserEntry($idno);
		$result = $openldap->updateData($entry, $userinfo);
		if (!$result) return back()->withInput()->with("error", "無法變更人員資訊！".$openldap->error());
		if ($request->has('login-by-email')) $accounts[] = $email;
		if ($request->has('login-by-mobile')) $accounts[] = $mobile;
		$openldap->updateAccounts($entry, $accounts);
		return back()->withInput()->with("success","您的個人資料設定已經儲存！");
    }

    public function showChangeAccountForm()
    {
		return view('auth.changeaccount');
    }

    public function changeAccount(Request $request)
    {
		$user = Auth::user();
		$old = $request->get('current-account');
		$new = $request->get('new-account');
		$openldap = new LdapServiceProvider();
		$accounts = $openldap->getUserAccounts($user->idno);
		$match = false;
		foreach ($accounts as $account) {
    		if ($old == $account) $match = true;
		}
		if (empty($accounts)) $match = true;
		if (!$match) return back()->withInput()->with("error","您輸入的帳號不正確，請您重新輸入一次！");

		if(strcmp($old, $new) == 0)
	    	return back()->withInput()->with("error","新帳號不可以跟舊的帳號相同，請重新想一個新帳號再試一次！");
		$validatedData = $request->validate([
			'new-account' => 'required|string|min:6|confirmed',
		]);
		if (!$openldap->accountAvailable($user->idno, $new))
			return back()->withInput()->with("error","您輸入的帳號已經被別人使用，請您重新輸入一次！");
		$entry = $openldap->getUserEntry($user->idno);
		if (empty($accounts)) {
			$openldap->addAccount($entry, $new, "自建帳號");
			Auth::logout();
			$user->notify(new AccountChangeNotification($new));
			return back()->withInput()->with("success","帳號建立成功！");
		} else {
			$openldap->renameAccount($entry, $old, $new);
			Auth::logout();
			$user->notify(new AccountChangeNotification($new));
			return back()->withInput()->with("success","帳號變更成功！");
		}
    }

    public function showChangePasswordForm()
    {
		return view('auth.changepassword');
    }

    public function changePassword(Request $request)
    {
		if (!(\Hash::check($request->get('current-password'), Auth::user()->password)))
	    	return back()->withInput()->with("error","您輸入的原密碼不正確，請您重新輸入一次！");
		if(strcmp($request->get('current-password'), $request->get('new-password')) == 0)
	    	return back()->withInput()->with("error","新密碼不可以跟舊的密碼相同，請重新想一個新密碼再試一次！");
		$validatedData = $request->validate([
			'current-password' => 'required',
			'new-password' => 'required|string|min:6|confirmed',
			]);
		$user = Auth::user();
		$pwd = $request->get('new-password');
		$user->resetLdapPassword($pwd);
		$user->password = \Hash::make($pwd);
		$user->save();
		Auth::logout();
		$user->notify(new PasswordChangeNotification($pwd));
		return back()->withInput()->with("success","密碼變更成功！");
    }

}
