<?php

namespace App\Http\Controllers\Api_V2;

use Auth;
use Cookie;
use App\Providers\LdapServiceProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class v2_profileController extends Controller
{
	public function valid_token(Request $request)
    {
		return response()->json(['data' => 'Token is valid!']);
	}

    public function logout(Request $request)
    {
		$request->session()->flush();
		$request->session()->regenerate();
		Cookie::queue(Cookie::forget('laravel_session', 'laravel_token'));
        return "<script>history.go(-1);</script>";
    }

    public function me(Request $request)
    {
		$user = $request->user();
        return response()->json([
        	"name" => $user->name,
            "email" => $user->email,
        ]);
    }

    public function email(Request $request)
    {
		$user = $request->user();
        return response()->json([
            "email" => $user->email,
        ]);
    }

    public function user(Request $request)
    {
		$user = $request->user();
		if (!isset($user->ldap)) return response()->json(["error" => "User not available!"], 400);
		$json = new \stdClass();
		$json->role = $user->ldap['employeeType'];
		$json->uuid = $user->uuid;
		$json->name = $user->name;
		$json->email = $user->email;
		$json->email_login = $user->ldap['email_login'];
		$json->mobile = $user->mobile;
		$json->mobile_login = $user->ldap['mobile_login'];
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function idno(Request $request)
    {
		$user = $request->user();
		$json = new \stdClass();
		$json->idno = $user->idno;
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function profile(Request $request)
    {
		$user = $request->user();
		if (!isset($user->ldap)) return response()->json(["error" => "User not available!"], 400);
		$json = new \stdClass();
		$json->role = $user->ldap['employeeType'];
		if (array_key_exists('gender', $user->ldap)) $json->gender = $user->ldap['gender'];
		if (array_key_exists('birthDate', $user->ldap)) $json->birthDate = $user->ldap['birthDate'];
		if (array_key_exists('o', $user->ldap)) $json->o = $user->ldap['o'];
		if (array_key_exists('school', $user->ldap)) $json->organization = $user->ldap['school'];
		if ($json->role == '學生') {
	    	if (array_key_exists('employeeNumber', $user->ldap)) $json->studentId = $user->ldap['employeeNumber'];
		    if (array_key_exists('tpClass', $user->ldap)) $json->class = $user->ldap['tpClass'];
			if (array_key_exists('tpClassTitle', $user->ldap)) $json->className = $user->ldap['tpClassTitle'];
	    	if (array_key_exists('tpSeat', $user->ldap)) $json->seat = $user->ldap['tpSeat'];
		} else {
		    if (array_key_exists('employeeNumber', $user->ldap)) $json->teacherId = $user->ldap['employeeNumber'];
	    	if (array_key_exists('department', $user->ldap)) $json->unit = (array) $user->ldap['department'];
	    	if (array_key_exists('titleName', $user->ldap)) $json->title = (array) $user->ldap['titleName'];
	    	if (array_key_exists('teachClass', $user->ldap)) $json->teachClass = (array) $user->ldap['teachClass'];
	    	if (array_key_exists('tpTutorClass', $user->ldap)) $json->tutorClass = $user->ldap['tpTutorClass'];
		}
		if (array_key_exists('tpCharacter', $user->ldap)) $json->character = $user->ldap['tpCharacter'];
    	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function updateUser(Request $request)
    {
		$openldap = new LdapServiceProvider();
		$user = $request->user();
		if (!isset($user->ldap)) return response()->json(["error" => "User not available!"], 400);
		$userinfo = array();
		$email = $request->get('email');
		$mobile = $request->get('mobile');
		$messages = '';
		if (!empty($email)) {
		    if ($email == $user->email) {
				return response()->json(["error" => "Email is the same as the old one!"], 400);
		    }
		    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				return response()->json(["error" => "Email invalid!"], 400);
		    }
	    	if (!$openldap->emailAvailable($user->idno, $email)) {
				return response()->json(["error" => "Email not available!"], 400);
	    	}
		    $userinfo['mail'] = $email;
		    $user->email = $userinfo['mail'];
	    	$messages = 'Email updated! ';
		}
		if (!empty($mobile)) {
		    if ($mobile == $user->mobile) {
				return response()->json(["error" => "Mobile is the same as the old one!"], 400);
		    }
		    if (!is_numeric($mobile) || strlen($mobile) != 10) {
				return response()->json(["error" => "Mobile invalid!"], 400);
		    }
		    if (!$openldap->mobileAvailable($user->idno, $mobile)) {
				return response()->json(["error" => "Mobile not available"], 400);
		    }
		    $userinfo['mobile'] = $mobile;
	    	$user->mobile = $userinfo['mobile'];
		    $messages .= 'Mobile updated! ';
		}
		$user->save();
		$entry = $openldap->getUserEntry($user->idno);
		$openldap->updateData($entry, $userinfo);
		$login_email = $request->get('email_login');
		if ($login_email == 'true') {
	    	if (array_key_exists('mail', $userinfo)) {
				$openldap->updateAccount($entry, $user->email, $userinfo['mail'], $user->idno, '電子郵件登入');
	    	} else {
				$openldap->addAccount($entry, $user->email, $user->idno, '電子郵件登入');
	    	}
	    	$messages .= 'Login by email is active! ';
		} elseif ($login_email == 'false') {
	    	$openldap->deleteAccount($entry, $user->email);
	    	$messages .= 'Login by email is inactive! ';
		}
		$login_mobile = $request->get('mobile_login');
		if ($login_mobile == 'true') {
	    	if (array_key_exists('mobile', $userinfo)) {
				$openldap->updateAccount($entry, $user->mobile, $userinfo['mobile'], $user->idno, '手機號碼登入');
	    	} else {
				$openldap->addAccount($entry, $user->mobile, $user->idno, '手機號碼登入');
	    	}
	    	$messages .= 'Login by mobile is active! ';
		} elseif ($login_mobile == 'false') {
	    	$openldap->deleteAccount($entry, $user->mobile);
	    	$messages .= 'Login by mobile is inactive! ';
		}
		if (empty($messages)) {
    	    return response()->json(["error" => "Request invalid!"], 400);
    	}
    	return response()->json(["success" => $messages], 200);
    }

    public function updateAccount(Request $request)
    {
		$openldap = new LdapServiceProvider();
		$user = $request->user();
		if (!isset($user->ldap)) return response()->json(["error" => "User not available!"], 400);
		$userinfo = array();
		$account = $request->get('account');
		$password = $request->get('password');
		$messages = '';
		if (is_array($user->ldap['uid'])) {
		    foreach ($user->ldap['uid'] as $uid) {
				if ($uid != $user->email && $uid != $user->mobile) $current = $uid;
		    }
		} else {
		    $current = $user->ldap['uid'];
		}
		if (!empty($account) && !empty($current)) {
		    if  ($account == $current) {
				return response()->json(["error" => "Account is the same as the old one!"], 400);
		    }
		    if (strlen($account) < 6) {
				return response()->json(["error" => "Account must be at least 6 characters!"], 400);
		    }
		    if (!$openldap->accountAvailable($user->idno, $account)) {
				return response()->json(["error" => "Account not available!"], 400);
		    }
		    $entry = $openldap->getUserEntry($user->idno);
	    	$openldap->renameAccount($entry, $current, $account);
		    $messages = 'Account updated! ';
		}
		if (!empty($password)) {
		    if (strlen($password) < 6) {
				return response()->json(["error" => "Password must be at least 6 characters!"], 400);
		    }
		    $user->resetLdapPassword($password);
	    	$user->password = \Hash::make($password);
		    $user->save();
		    $messages .= 'Password updated!';
		}
		if (empty($messages)) {
    	    return response()->json(["error" => "Request invalid!"], 400);
    	}
    	return response()->json(["success" => $messages], 200);
    }
}
