<?php

namespace App\Http\Controllers\Api_V2;

use Cookie;
use App\Providers\LdapServiceProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use App\PSLink;

class v2_profileController extends Controller
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

        return response()->json($json);
    }

    public function profile(Request $request)
    {
        $openldap = new LdapServiceProvider();
        $user = $request->user();
        if ($user->is_parent) {
            $json = new \stdClass();
            $json->role = '家長';
            $kids = PSLink::where('parent_idno', $user->idno)->where('verified', 1)->get();
            foreach ($kids as $kid) {
                $idno = $kid->student_idno;
                $uuid = $openldap->getUserUUID($idno);
                if ($uuid) {
                    $json->child[] = $uuid;
                }
            }
        } else {
            if (!isset($user->ldap) || !$user->ldap) {
                return response()->json(['error' => '人員不存在'], 400, array(JSON_UNESCAPED_UNICODE));
            }
            $json = new \stdClass();
            $json->role = $user->ldap['employeeType'];
            if (array_key_exists('gender', $user->ldap)) {
                $json->gender = $user->ldap['gender'];
            }
            if (array_key_exists('birthDate', $user->ldap)) {
                $json->birthDate = $user->ldap['birthDate'];
            }
            if (array_key_exists('o', $user->ldap)) {
                $json->o = $user->ldap['o'];
            }
            if (array_key_exists('school', $user->ldap)) {
                $json->organization = $user->ldap['school'];
            }
            if ($json->role == '學生') {
                if (array_key_exists('employeeNumber', $user->ldap)) {
                    $json->studentId = $user->ldap['employeeNumber'];
                }
                if (array_key_exists('tpClass', $user->ldap)) {
                    $json->class = $user->ldap['tpClass'];
                }
                if (array_key_exists('tpClassTitle', $user->ldap)) {
                    $json->className = $user->ldap['tpClassTitle'];
                }
                if (array_key_exists('tpSeat', $user->ldap)) {
                    $json->seat = $user->ldap['tpSeat'];
                }
                $kids = PSLink::where('student_idno', $user->idno)->where('verified', 1)->get();
                foreach ($kids as $kid) {
                    $idno = $kid->parent_idno;
                    $uuid = false;
                    $parent = User::where('idno', $idno)->first();
                    if ($parent) {
                        $uuid = $parent->uuid;
                    }
                    if ($uuid) {
                        $json->parent[] = $uuid;
                    }
                }
            } else {
                if (array_key_exists('employeeNumber', $user->ldap)) {
                    $json->teacherId = $user->ldap['employeeNumber'];
                }
                if (array_key_exists('department', $user->ldap)) {
                    $json->unit = (array) $user->ldap['department'];
                }
                if (array_key_exists('titleName', $user->ldap)) {
                    $json->title = (array) $user->ldap['titleName'];
                }
                if (array_key_exists('teachClass', $user->ldap)) {
                    $json->teachClass = (array) $user->ldap['teachClass'];
                }
                if (array_key_exists('tpTutorClass', $user->ldap)) {
                    $json->tutorClass = $user->ldap['tpTutorClass'];
                }
                $kids = PSLink::where('parent_idno', $user->idno)->where('verified', 1)->get();
                foreach ($kids as $kid) {
                    $idno = $kid->student_idno;
                    $uuid = $openldap->getUserUUID($idno);
                    if ($uuid) {
                        $json->child[] = $uuid;
                    }
                }
            }
            if (array_key_exists('tpCharacter', $user->ldap)) {
                $json->character = $user->ldap['tpCharacter'];
            }
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
