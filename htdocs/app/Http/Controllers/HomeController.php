<?php

namespace App\Http\Controllers;

use Auth;
use Config;
use Illuminate\Http\Request;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;

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
		$userinfo = array();
		if ($email != $user->email) {
	    	$validatedData = $request->validate([
			    'email' => 'required|email|unique:users',
			]);
	    	if (!$openldap->emailAvailable($user->idno, $email))
				return redirect()->back()->with("error","您輸入的電子郵件已經被別人使用，請您重新輸入一次！");
	    	$userinfo['mail'] = $email;
	    	$user->email = $userinfo['mail'];
		}
		if ($mobile != $user->mobile) {
	    	$validatedData = $request->validate([
			    'mobile' => 'string|digits:10|numeric',
			]);
	    	if (!$openldap->mobileAvailable($user->idno, $mobile))
				return redirect()->back()->with("error","您輸入的手機號碼已經被別人使用，請您重新輸入一次！");
	    	$userinfo['mobile'] = $mobile;
	    	$user->mobile = $userinfo['mobile'];
		}
		$entry = $openldap->getUserEntry($user->idno);
		$openldap->updateData($entry, $userinfo);
		if ($request->has('login-by-email')) {
	    	if (array_key_exists('mail', $userinfo)) {
				$openldap->updateAccount($entry, $user->email, $userinfo['mail'], $user->idno, '電子郵件登入');
	    	} else {
				$openldap->addAccount($entry, $user->email, $user->idno, '電子郵件登入');
	    	}
		} else {
	    	$openldap->deleteAccount($entry, $user->email);
		}
		if ($request->has('login-by-mobile')) {
	    	if (array_key_exists('mobile', $userinfo)) {
				$openldap->updateAccount($entry, $user->mobile, $userinfo['mobile'], $user->idno, '手機號碼登入');
	    	} else {
				$openldap->addAccount($entry, $user->mobile, $user->idno, '手機號碼登入');
	    	}
		} else {
	    	$openldap->deleteAccount($entry, $user->mobile);
		}
		$user->save();
		return redirect()->back()->with("success","您的個人資料設定已經儲存！");
    }

    public function showChangeAccountForm()
    {
		return view('auth.changeaccount');
    }

    public function changeAccount(Request $request)
    {
		$user = Auth::user();
		$accounts = array();
		if (array_key_exists('uid',$user->ldap)) {
			if (is_array($user->ldap['uid'])) {
		    	$accounts = $user->ldap['uid'];
			} else {
	    		$accounts[] = $user->ldap['uid'];
			}
			$match = false;
			foreach ($accounts as $account) {
	    		if ($account != $user->email) {
					if (array_key_exists('mobile', $user->ldap)) {
		    			if  ($account != $user->ldap['mobile']) {
							if ($request->get('current-account') == $account) $match = true;
		    			}
					} else {
		    			if ($request->get('current-account') == $account) $match = true;
					}
	    		}
				if (!$match) return redirect()->back()->with("error","您輸入的帳號不正確，請您重新輸入一次！");
			}
		}
		if(strcmp($request->get('current-account'), $request->get('new-account')) == 0)
	    return redirect()->back()->with("error","新帳號不可以跟舊的帳號相同，請重新想一個新帳號再試一次！");
		$validatedData = $request->validate([
			'new-account' => 'required|string|min:6|confirmed',
		]);
		$openldap = new LdapServiceProvider();
		if (!$openldap->accountAvailable($user->idno, $request->get('new-account')))
	    	return redirect()->back()->with("error","您輸入的帳號已經被別人使用，請您重新輸入一次！");
		$entry = $openldap->getUserEntry($user->idno);
		if (empty($request->get('current-account'))) {
			$openldap->addAccount($entry, $request->get('new-account'), $user->idno, "自建帳號");
			return redirect()->back()->with("success","帳號建立成功！");
		} else {
			$openldap->renameAccount($entry, $request->get('current-account'), $request->get('new-account'));
			return redirect()->back()->with("success","帳號變更成功！");
		}
    }

    public function showChangePasswordForm()
    {
		return view('auth.changepassword');
    }

    public function changePassword(Request $request)
    {
		if (!(\Hash::check($request->get('current-password'), Auth::user()->password)))
	    	return redirect()->back()->with("error","您輸入的原密碼不正確，請您重新輸入一次！");
		if(strcmp($request->get('current-password'), $request->get('new-password')) == 0)
	    	return redirect()->back()->with("error","新密碼不可以跟舊的密碼相同，請重新想一個新密碼再試一次！");
		$validatedData = $request->validate([
			'current-password' => 'required',
			'new-password' => 'required|string|min:6|confirmed',
			]);
		$user = Auth::user();
		$pwd = $request->get('new-password');
		$user->resetLdapPassword($pwd);
		$user->password = \Hash::make($pwd);
		$user->save();
		return redirect()->back()->with("success","密碼變更成功！");
    }

}
