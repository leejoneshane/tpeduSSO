<?php

namespace App\Http\Controllers;

use Log;
use Auth;
use App\User;
use Config;
use Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Providers\LdapServiceProvider;
use App\Providers\GoogleServiceProvider;
use App\Rules\idno;
use App\Notifications\AccountChangeNotification;
use App\Notifications\PasswordChangeNotification;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$user = Auth::user();
		if (!isset($user->email) || empty($user->email)) return redirect()->route('profile');

		if ($user->is_parent) {
			return redirect()->route('parent');
		} else {
			$openldap = new LdapServiceProvider();
			$account = $user->account();
			$gsuite = $user->nameID();
			$account_ready = true;
			if (empty($account) || $user->is_default_account()) {
				$account_ready = false;
			}
			$gsuite_ready = false;
			if ($gsuite) $gsuite_ready = true;
			$create_gsuite = false;
			if (!$gsuite_ready && $account_ready) {
				$create_gsuite = true;
				$gsuite = $account;
			}
			$gmail = '';
			if (!empty($gsuite)) $gmail = $gsuite .'@'. Config::get('saml.email_domain');
			return view('home', [ 'account_ready' => $account_ready, 'create_gsuite' => $create_gsuite, 'gsuite_ready' => $gsuite_ready, 'gsuite' => $gmail ]);	
		}
    }
    
    public function showProfileForm(Request $request)
    {
		return view('auth.profile', [ 'user' => Auth::user() ]);
    }

    public function changeProfile(Request $request)
    {
		$email = $request->get('email');
		$mobile = $request->get('mobile');
		$user = Auth::user();
		$idno = $user->idno;
		$userinfo = array();
		$openldap = new LdapServiceProvider();
		if ($email && $email != $user->email) {
	    	$validatedData = $request->validate([
			    'email' => 'required|email|unique:users',
			]);
	    	if (!$openldap->emailAvailable($idno, $email))
				return back()->withInput()->with("error","您輸入的電子郵件已經被別人使用，請您重新輸入一次！");
	    	$userinfo['mail'] = $email;
	    	$user->email = $email;
		}
		if (!$email) {
    		$userinfo['email'] = array();
    		$user->email = null;
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
		if (!$user->is_parent) {
			$accounts = $openldap->getUserAccounts($idno);
			$entry = $openldap->getUserEntry($idno);
			$result = $openldap->updateData($entry, $userinfo);
			if (!$result) return back()->withInput()->with("error", "無法變更人員資訊！".$openldap->error());
			if ($request->get('login-by-email', 'no') == "yes" && !empty($email)) $accounts[] = $email;
			if ($request->get('login-by-mobile', 'no') == "yes" && !empty($mobile)) $accounts[] = $mobile;
			$accounts = array_values(array_unique($accounts));
			$openldap->updateData($entry, array( 'uid' => $accounts));
			$openldap->updateAccounts($entry, $accounts);
		}
		return back()->withInput()->with("success","您的個人資料設定已經儲存！");
    }

    public function showChangeAccountForm(Request $request)
    {
		if (Auth::check()) {
			$user = Auth::user();
			if ($user->is_parent) return redirect('parent');
		}
		return view('auth.changeaccount');
    }

    public function changeAccount(Request $request)
    {
		if (Auth::check()) {
			$user = Auth::user();
			$idno = $user->idno;
			if ($user->is_parent) return redirect('parent');
		} else {
			$idno = $request->session()->get('idno');
		}
		$validatedData = $request->validate([
			'new-account' => 'required|alpha_num|min:6',
		]);
		$new = $request->get('new-account');
		$openldap = new LdapServiceProvider();
		$accounts = $openldap->getUserAccounts($idno);
		foreach ($accounts as $account) {
    		if ($new == $account) return back()->withInput()->with("error","新帳號不可以跟舊的帳號相同，請重新想一個新帳號再試一次！");
		}
		if($idno == $new) return back()->withInput()->with("error","新帳號不可以跟身分證字號相同，請重新想一個新帳號再試一次！");
		if (!$openldap->accountAvailable($new)) return back()->withInput()->with("error","您輸入的帳號已經被別人使用，請您重新輸入一次！");
		$entry = $openldap->getUserEntry($idno);
		$data = $openldap->getUserData($entry, 'mail');
		if (empty($accounts)) {
			//	建立 gmail account 尚未設計
			$openldap->addAccount($entry, $new, "自建帳號");
			if (Auth::check()) {
				if ($user->hasVerifiedEmail()) $user->notify(new PasswordChangeNotification($new));
			} else {
				if (isset($data['mail'])) Notification::route('mail', $data['mail'])->notify(new AccountChangeNotification($new));
			}
			return back()->withInput()->with("success","帳號建立成功！");
		} else {
			//	建立 gmail alias 尚未設計
			$openldap->renameAccount($entry, $new);
			if (Auth::check()) {
				if ($user->hasVerifiedEmail()) $user->notify(new PasswordChangeNotification($new));
			} else {
				if (isset($data['mail'])) Notification::route('mail', $data['mail'])->notify(new AccountChangeNotification($new));
			}
			$mustChangePW = $request->session()->pull('mustChangePW', false);
			if ($mustChangePW) {
				$request->session()->put('idno', $idno);
				return redirect()->route('changePassword')->with("success","帳號變更成功，要先修改密碼才能執行後續作業！");
			} else {
				$request->session()->invalidate();
				return redirect('login')->with("success","帳號變更成功，請重新登入！");
			}
		}
    }

    public function showChangePasswordForm(Request $request)
    {
		return view('auth.changepassword');
    }

    public function changePassword(Request $request)
    {
		if (Auth::check()) {
			$user = Auth::user();
			$idno = $user->idno;
		} else {
			$idno = $request->session()->get('idno');
			$user = User::where('idno', $idno)->first();
		}
		$validatedData = $request->validate([
			'new-password' => 'required|string|min:6|confirmed',
		]);
		$new = $request->get('new-password');
		if ($user && $user->is_parent) {
			if (Hash::check($new, $user->password)) return back()->withInput()->with("error","新密碼不可以跟舊的密碼相同，請重新想一個新密碼再試一次！");
			if ($new == $user->email) return back()->withInput()->with("error","新密碼不可以跟帳號相同，請重新想一個新密碼再試一次！");
			User::find($user->id)->update(['password'=> Hash::make($new)]);
			$request->session()->invalidate();
			return redirect('login')->with("success","密碼變更成功，請重新登入！");
		} else {
			$openldap = new LdapServiceProvider();
			$entry = $openldap->getUserEntry($idno);
			$data = $openldap->getUserData($entry);
			if ($openldap->userLogin("cn=$idno", $new))
				return back()->withInput()->with("error","新密碼不可以跟舊的密碼相同，請重新想一個新密碼再試一次！");
			$accounts = $openldap->getUserAccounts($idno); 
			if (!empty($accounts) && $accounts[0] == $new)
				return back()->withInput()->with("error","新密碼不可以跟帳號相同，請重新想一個新密碼再試一次！");
			if (Auth::check()) {
				$user->resetLdapPassword($new);
				$user->password = \Hash::make($new);
				$user->save();
				if ($user->hasVerifiedEmail()) $user->notify(new PasswordChangeNotification($new));
			} else {
				$openldap->resetPassword($entry, $new);
				if ($user) {
					$user->password = \Hash::make($new);
					$user->save();
				}
				if (isset($data['mail'])) Notification::route('mail', $data['mail'])->notify(new PasswordChangeNotification($new));
			}
			$request->session()->invalidate();
			return redirect('login')->with("success","密碼變更成功，請重新登入！");
		}
    }

	public function syncToGsuite(Request $request)
    {
		$user = Auth::user();
		$google = new GoogleServiceProvider();
		$result = $google->sync($user);
		if ($result) {
			return redirect('/')->with("status","G-Suite 帳號同步完成！");
		} else {
			return redirect('/')->with("status","G-Suite 帳號同步失敗！");
		}
    }

}
