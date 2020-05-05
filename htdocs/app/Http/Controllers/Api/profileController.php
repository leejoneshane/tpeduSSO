<?php

namespace App\Http\Controllers\Api;

use Cookie;
use App\Providers\LdapServiceProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class profileController extends Controller
{
    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerate();
        Cookie::queue(Cookie::forget('laravel_session', 'laravel_token'));
        if ($request->has('redirect')) {
            $url = $request->get('redirect');
            if (!empty($url)) {
                return "<script>location='$url';</script>";
            }
        }

        return '<script>history.go(-1);</script>';
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function email(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'email' => $user->email,
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        if ($user->is_parent) {
            $json = new \stdClass();
            $json->role = '家長';
            $json->uuid = $user->uuid;
            $json->name = $user->name;
            $json->email = $user->email;
            $json->mobile = $user->mobile;
        } else {
            if (!isset($user->ldap) || !$user->ldap) {
                return response()->json(['error' => '人員不存在'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            $json = new \stdClass();
            $json->role = $user->ldap['employeeType'];
            $json->uuid = $user->uuid;
            $json->name = $user->name;
            $json->email = $user->email;
            $json->email_login = $user->ldap['email_login'];
            $json->mobile = $user->mobile;
            $json->mobile_login = $user->ldap['mobile_login'];
        }

        return response()->json($json, 200, array(JSON_UNESCAPED_UNICODE));
    }

    public function idno(Request $request)
    {
        $user = $request->user();
        $json = new \stdClass();
        $json->idno = $user->idno;

        return response()->json($json, 200);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        if (!isset($user->ldap)) {
            return response()->json(['error' => 'User not available!'], 400);
        }
        $json = new \stdClass();
        $json->role = $user->ldap['employeeType'];
        if (isset($user->ldap['o']) && !empty($user->ldap['o'])) {
            if (is_array($user->ldap['o'])) {
                $o = $user->ldap['o'][0];
            } else {
                $o = $user->ldap['o'];
            }
            $json->o = $o;
            if (isset($user->ldap['school'][$o]) && !empty($user->ldap['school'][$o])) {
                $json->organization = $user->ldap['school'][$o];
            }
        }
        if (isset($user->ldap['gender']) && !empty($user->ldap['gender'])) {
            $json->gender = $user->ldap['gender'];
        }
        if (isset($user->ldap['birthDate']) && !empty($user->ldap['birthDate'])) {
            $json->birthDate = $user->ldap['birthDate'];
        }
        if ($json->role == '學生') {
            if (isset($user->ldap['employeeNumber']) && !empty($user->ldap['employeeNumber'])) {
                $json->studentId = $user->ldap['employeeNumber'];
            }
            if (isset($user->ldap['tpClass']) && !empty($user->ldap['tpClass'])) {
                $json->class = $user->ldap['tpClass'];
            }
            if (isset($user->ldap['tpClassTitle']) && !empty($user->ldap['tpClassTitle'])) {
                $json->className = $user->ldap['tpClassTitle'];
            }
            if (isset($user->ldap['tpSeat']) && !empty($user->ldap['tpSeat'])) {
                $json->seat = $user->ldap['tpSeat'];
            }
        } else {
            if (isset($o) && $o) {
                if (isset($user->ldap['department'][$o]) && !empty($user->ldap['department'][$o])) {
                    $json->unit = $user->ldap['department'][$o][0]->name;
                }
                if (isset($user->ldap['titleName'][$o]) && !empty($user->ldap['titleName'][$o])) {
                    $json->title = $user->ldap['titleName'][$o][0]->name;
                }
                if (isset($user->ldap['teachClass'][$o]) && !empty($user->ldap['teachClass'][$o])) {
                    $json->teachClass = $user->ldap['teachClass'][$o];
                }
                if (isset($user->ldap['tpTutorClass']) && !empty($user->ldap['tpTutorClass'])) {
                    $json->tutorClass = $user->ldap['tpTutorClass'];
                }
            }
        }
        if (isset($user->ldap['tpCharacter']) && !empty($user->ldap['tpCharacter'])) {
            $json->character = $user->ldap['tpCharacter'];
        }

        return response()->json($json, 200, array(JSON_UNESCAPED_UNICODE));
    }

    public function updateUser(Request $request)
    {
        $openldap = new LdapServiceProvider();
        $user = $request->user();
        if ($user->is_parent) {
            return response()->json(['error' => '家長帳號不支援此功能！'], 400, array(JSON_UNESCAPED_UNICODE));
        }
        if (!isset($user->ldap) || !$user->ldap) {
            return response()->json(['error' => '人員不存在'], 400, array(JSON_UNESCAPED_UNICODE));
        }
        $userinfo = array();
        $email = $request->get('email');
        $mobile = $request->get('mobile');
        $messages = '';
        if (!empty($email)) {
            if ($email == $user->email) {
                return response()->json(['error' => '新電子郵件信箱不可以和舊的相同'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => '電子郵件信箱格式不正確'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (!$openldap->emailAvailable($user->idno, $email)) {
                return response()->json(['error' => '電子郵件信箱已經被使用'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            $userinfo['mail'] = $email;
            $user->email = $userinfo['mail'];
            $messages = '電子郵件信箱更新完成 ';
        }
        if (!empty($mobile)) {
            if ($mobile == $user->mobile) {
                return response()->json(['error' => '新行動電話不可以和舊的相同'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (!is_numeric($mobile) || strlen($mobile) != 10) {
                return response()->json(['error' => '行動電話格式不正確'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (!$openldap->mobileAvailable($user->idno, $mobile)) {
                return response()->json(['error' => '行動電話已經被使用'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            $userinfo['mobile'] = $mobile;
            $user->mobile = $userinfo['mobile'];
            $messages .= '行動電話更新完成 ';
        }
        $user->save();
        $entry = $openldap->getUserEntry($user->idno);
        $openldap->updateData($entry, $userinfo);
        $login_email = $request->get('email_login');
        if ($login_email == 'true') {
            if (array_key_exists('mail', $userinfo)) {
                $openldap->updateAccounts($entry, $user->email, $userinfo['mail'], $user->idno, '電子郵件登入');
            } else {
                $openldap->addAccount($entry, $user->email, $user->idno, '電子郵件登入');
            }
            $messages .= '使用電子郵件信箱登入的功能已經開啟 ';
        } elseif ($login_email == 'false') {
            $openldap->deleteAccount($entry, $user->email);
            $messages .= '使用電子郵件信箱登入的功能已經關閉 ';
        }
        $login_mobile = $request->get('mobile_login');
        if ($login_mobile == 'true') {
            if (array_key_exists('mobile', $userinfo)) {
                $openldap->updateAccounts($entry, $user->mobile, $userinfo['mobile'], $user->idno, '手機號碼登入');
            } else {
                $openldap->addAccount($entry, $user->mobile, $user->idno, '手機號碼登入');
            }
            $messages .= '使用行動電話登入的功能已經開啟 ';
        } elseif ($login_mobile == 'false') {
            $openldap->deleteAccount($entry, $user->mobile);
            $messages .= '使用行動電話登入的功能已經關閉 ';
        }
        if (empty($messages)) {
            return response()->json(['error' => '更新帳號資訊時發生錯誤'], 400, array(JSON_UNESCAPED_UNICODE));
        }

        return response()->json(['success' => $messages], 200, array(JSON_UNESCAPED_UNICODE));
    }

    public function updateAccount(Request $request)
    {
        $openldap = new LdapServiceProvider();
        $user = $request->user();
        if ($user->is_parent) {
            return response()->json(['error' => '家長帳號不支援此功能！'], 400, array(JSON_UNESCAPED_UNICODE));
        }
        if (!isset($user->ldap) || !$user->ldap) {
            return response()->json(['error' => '人員不存在'], 400, array(JSON_UNESCAPED_UNICODE));
        }
        $userinfo = array();
        $account = $request->get('account');
        $password = $request->get('password');
        $messages = '';
        if (is_array($user->ldap['uid'])) {
            foreach ($user->ldap['uid'] as $uid) {
                if ($uid != $user->email && $uid != $user->mobile) {
                    $current = $uid;
                }
            }
        } else {
            $current = $user->ldap['uid'];
        }
        if (!empty($account) && !empty($current)) {
            if ($account == $current) {
                return response()->json(['error' => '新帳號不可以與舊帳號相同'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (strlen($account) < 6) {
                return response()->json(['error' => '帳號至少要六個字元'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (!$openldap->accountAvailable($user->idno, $account)) {
                return response()->json(['error' => '帳號已經被使用'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (is_numeric($account)) {
                return response()->json(['error' => '帳號應包含數字以外的字元'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            if (strpos($account, '@')) {
                return response()->json(['error' => '帳號不可以是電子郵件'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            $entry = $openldap->getUserEntry($user->idno);
            $openldap->renameAccount($entry, $current, $account);
            $messages = '帳號更新完成 ';
        }
        if (!empty($password)) {
            if (strlen($password) < 6) {
                return response()->json(['error' => '密碼至少要六個字元'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            $user->resetLdapPassword($password);
            $user->password = \Hash::make($password);
            $user->save();
            $messages .= '密碼更新完成';
        }
        if (empty($messages)) {
            return response()->json(['error' => '更新帳號資訊時發生錯誤'], 400, array(JSON_UNESCAPED_UNICODE));
        }

        return response()->json(['success' => $messages], 200, array(JSON_UNESCAPED_UNICODE));
    }
}
