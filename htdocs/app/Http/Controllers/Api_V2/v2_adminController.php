<?php

namespace App\Http\Controllers\Api_V2;

use Cookie;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Token;
use Laravel\Passport\Client;
use App\User;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;

class v2_adminController extends Controller
{
	public function valid_token(Request $request, $token)
    {
		$openldap = new LdapServiceProvider();
		$psr = (new \Lcobucci\JWT\Parser())->parse($token);
		$token_id = $psr->getClaim('jti');
		$token = Token::find($token_id);
		if (!$token) {
			return response()->json(['error' => 'The token is invliad!'], 404);
		} elseif ($token->revoked) {
			return response()->json(['error' => 'The token is revoked!'], 410);
		} else {
			$user = $token->user;
			$validate = array();
			if (isset($user->uuid)) $validate['user'] = $user->uuid;
			if (!empty($token->name)) {
                $entry = $openldap->getUserEntry($user->uuid);
                $admin = $openldap->getUserData($entry, 'tpAdminSchools');
                $validate['admin_schools'] = $admin['tpAdminSchools'];
            }
			$validate['client_id'] = $token->client_id;
			$validate['scopes'] = $token->scopes;
			return response()->json($validate);
		}
	}

    public function schoolAdd(Request $request)
    {
		$validatedData = $request->validate([
			'o' => 'required|string',
			'name' => 'required|string',
			'type' => 'required|string',
			'area' => 'required|string',
			'uno' => 'required|string|size:6',
			'fax' => 'nullable|string',
			'tel' => 'nullable|string',
			'postal' => 'nullable|digits_between:3,5',
			'address' => 'nullable|string',
			'mbox' => 'nullable|digits:3',
			'www' => 'nullable|url',
			'ipv4' => new ipv4cidr,
			'ipv6' => new ipv6cidr,
		]);
        $schoolinfo = array();
		$openldap = new LdapServiceProvider();
		$dc = $request->get('o');
		$schoolinfo['objectClass'] = array('tpeduSchool','top');
		$schoolinfo['o'] = $dc;
		$schoolinfo['businessCategory'] = $request->get('type');
        $schoolinfo['st'] = $request->get('area');
		$schoolinfo['description'] = $request->get('name');
		$schoolinfo['tpUniformNumbers'] = $request->get('uno');
		if (!empty($request->get('fax'))) $schoolinfo['facsimileTelephoneNumber'] = $request->get('fax');
		if (!empty($request->get('tel'))) $schoolinfo['telephoneNumber'] = $request->get('tel');
		if (!empty($request->get('postal'))) $schoolinfo['postalCode'] = $request->get('postal');
		if (!empty($request->get('address'))) $schoolinfo['street'] = $request->get('address');
		if (!empty($request->get('mbox'))) $schoolinfo['postOfficeBox'] = $request->get('mbox');
		if (!empty($request->get('www'))) $schoolinfo['wWWHomePage'] = $request->get('www');
		if (!empty($request->get('ipv4'))) $schoolinfo['tpIpv4'] = $request->get('ipv4');
		if (!empty($request->get('ipv6'))) $schoolinfo['tpIpv6'] = $request->get('ipv6');
		if (!empty($request->get('admins'))) $schoolinfo['tpAdministrator'] = $request->get('admins');
		$schoolinfo['dn'] = "dc=$dc,".Config::get('ldap.rdn');
		if (!$openldap->createEntry($schoolinfo)) {
		    return response()->json([ 'error' => '教育機構建立失敗：' . $openldap->error() ], 500);
		}
		$openldap->updateOus($dc, $request->get('ous'));
		$openldap->updateClasses($dc, $request->get('classes'));
		$openldap->updateSubjects($dc, $request->get('subjects'));
		$entry = $openldap->getOrgEntry($dc);
		$openldap->updateData($entry, $schoolinfo);
		$json = $openldap->getOrgData($entry);
		return response()->json($json, 200, JSON_UNESCAPED_UNICODE);
    }

    public function schoolUpdate(Request $request, $dc)
    {
		$validatedData = $request->validate([
			'name' => 'nullable|string',
			'type' => 'nullable|string',
			'area' => 'nullable|string',
			'fax' => 'nullable|string',
			'tel' => 'nullable|string',
			'postal' => 'nullable|digits_between:3,5',
			'address' => 'nullable|string',
			'mbox' => 'nullable|digits:3',
			'www' => 'nullable|url',
			'uno' => 'nullable|string|size:6',
			'ipv4' => new ipv4cidr,
			'ipv6' => new ipv6cidr,
		]);
        $schoolinfo = array();
		$openldap = new LdapServiceProvider();
        if (!empty($request->get('area'))) $schoolinfo['st'] = $request->get('area');
		if (!empty($request->get('name'))) $schoolinfo['description'] = $request->get('name');
		if (!empty($request->get('fax'))) $schoolinfo['facsimileTelephoneNumber'] = $request->get('fax');
		if (!empty($request->get('tel'))) $schoolinfo['telephoneNumber'] = $request->get('tel');
		if (!empty($request->get('postal'))) $schoolinfo['postalCode'] = $request->get('postal');
		if (!empty($request->get('address'))) $schoolinfo['street'] = $request->get('address');
		if (!empty($request->get('mbox'))) $schoolinfo['postOfficeBox'] = $request->get('mbox');
		if (!empty($request->get('www'))) $schoolinfo['wWWHomePage'] = $request->get('www');
		if (!empty($request->get('uno'))) $schoolinfo['tpUniformNumbers'] = $request->get('uno');
		if (!empty($request->get('ipv4'))) $schoolinfo['tpIpv4'] = $request->get('ipv4');
		if (!empty($request->get('ipv6'))) $schoolinfo['tpIpv6'] = $request->get('ipv6');
		if (!empty($request->get('admins'))) $schoolinfo['tpAdministrator'] = $request->get('admins');
		$openldap->updateOus($dc, $request->get('ous'));
		$openldap->updateClasses($dc, $request->get('classes'));
		$openldap->updateSubjects($dc, $request->get('subjects'));
		$entry = $openldap->getOrgEntry($dc);
		$openldap->updateData($entry, $schoolinfo);
		$json = $openldap->getOrgData($entry);
		return response()->json($json, 200, JSON_UNESCAPED_UNICODE);
    }

	public function schoolRemove(Request $request, $dc)
    {
        $openldap = new LdapServiceProvider();
        $entry = $openldap->getOrgEntry($dc);
		if (!$entry) return response()->json([ 'error' => '找不到指定的教育機構'], 404);
		$result = $openldap->deleteEntry($entry);
		if ($result)
		    return response()->json([ 'success' => '指定的教育機構已經刪除'], 410);
		else
		    return response()->json([ 'error' => '指定的教育機構刪除失敗：' . $openldap->error() ], 500);
    }

    public function peopleSearch(Request $request)
    {
        $openldap = new LdapServiceProvider();
        $condition = array();
        $org = $request->get('o');
        if ($org) $condition[] = "(o=$org)";
        $role = $request->get('role');
        if ($role) $condition[] = "(employeeType=$role)";
        $idno = $request->get('idno');
        if ($idno) $condition[] = "(cn=$idno)";
        $uid = $request->get('uid');
        if ($uid) $condition[] = "(uid=$uid)";
        $sysid = $request->get('sysid');
        if ($sysid) $condition[] = "(employeeNumber=$sysid)";
        $gender = $request->get('gender');
        if ($gender) $condition[] = "(gender=$gender)";
        $name = $request->get('name');
        if ($name) $condition[] = "(displayName=*$name*)";
        $email = $request->get('email');
        if ($email) $condition[] = "(mail=*$email*)";
        $tel = $request->get('tel');
        if ($tel) $condition[] = "(|(mobile=$tel)(telephoneNumber=$tel))";
        if (count($condition) == 0) {
            return response()->json([ 'error' => '請提供搜尋條件'], 500);
        } else {
            $filter = '(&';
            foreach ($condition as $c) {
                $filter .= $c;
            }
        
            $filter .= '(inetUserStatus=active))';
            $people = $openldap->findUsers($filter, "entryUUID");
            $json = array();
            if ($people)
	    	    foreach ($people as $one) {
	        	    $json[] = $one['entryUUID'];
		        }
            if ($json)
                return response()->json($json, 200, JSON_UNESCAPED_UNICODE);
            else
                return response()->json([ 'error' => "找不到符合條件的人員"], 404);
        }
    }

    public function peopleAdd(Request $request)
    {
		$validatedData = $request->validate([
			'school' => 'required|string',
			'idno' => new idno,
			'lastname' => 'required|string',
			'firstname' => 'required|string',
			'type' => 'required|string',
		]);
        $openldap = new LdapServiceProvider();
        $idno = strtoupper($request->get('idno'));
        if (empty($idno)) return response()->json(["error" => "請提供身分證字號"], 400);
        $entry = $openldap->getUserEntry($idno);
        if ($entry) return response()->json(["error" => "該使用者已經存在"], 400);
        if (empty($request->get('type'))) return response()->json(["error" => "請提供該使用者的身份"], 400);
        if (empty($request->get('lastname')) || empty($request->get('firstname'))) return response()->json(["error" => "請提供該使用者的真實姓名"], 400);
		$info = array();
		$info['dn'] = "cn=".$idno.",".Config::get('ldap.userdn');
		$info["objectClass"] = array("tpeduPerson","inetUser");
 		$info["inetUserStatus"] = "Active";
        $info["cn"] = $idno;
	    if (!empty($request->get('password'))) {
            $info["userPassword"] = $openldap->make_ssha_password($request->get('password'));
        } else {
            $info["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
        }
        $orgs = array();
        $sch = $request->get('school');
        if (empty($sch)) return response()->json(["error" => "請提供所屬機關學校"], 400);
        if (is_array($sch))
            $orgs = $sch;
        else
            $orgs[] = $sch;
        $educloud = array();
		foreach ($orgs as $o) {
			$entry = $openldap->getOrgEntry($o);
			$data = $openldap->getOrgData($entry, 'tpUniformNumbers');
			$sid = $data['tpUniformNumbers'];
			$educloud[] = json_encode(array("sid" => $sid, "role" => $request->get('type')), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		}
        $info['o'] = $orgs;
        $info['info'] = $educloud;
		$info['employeeType'] = $request->get('type');
		$info['sn'] = $request->get('lastname');
		$info['givenName'] = $request->get('firstname');
		$info['displayName'] = $info['sn'].$info['givenName'];
        if ($request->get('type') != "學生") {
            if (!empty($request->get('unit'))) $info['ou'] = $request->get('unit');
            if (!empty($request->get('role'))) $info['title'] = $request->get('role');
            if (!empty($request->get('tclass'))) $info['tpTeachClass'] = $request->get('tclass');
        } else {
            if (empty($request->get('stdno'))) return response()->json(["error" => "請提供學號"], 400);
            $info['employeeNumber'] = $request->get('stdno');
            if (empty($request->get('class'))) return response()->json(["error" => "請提供就讀班級"], 400);
            $info['tpClass'] = $request->get('class');
            if (empty($request->get('seat'))) return response()->json(["error" => "請提供學生座號"], 400);
            $info['tpSeat'] = $request->get('seat');
        }
		if (!empty($request->get('memo'))) $info['tpCharacter'] = $request->get('memo');
		if (!empty($request->get('gender'))) $info['gender'] = $request->get('gender');
		if (!empty($request->get('birthdate'))) $info['birthDate'] = $request->get('birthdate')."000000Z";
		if (!empty($request->get('email'))) $info['mail'] = $request->get('email');
		if (!empty($request->get('mobile'))) $info['mobile'] = $request->get('mobile');
		if (!empty($request->get('fax'))) $info['facsimileTelephoneNumber'] = $request->get('fax');
		if (!empty($request->get('otel'))) $info['telephoneNumber'] = $request->get('otel');
		if (!empty($request->get('htel'))) $info['homePhone'] = $request->get('htel');
		if (!empty($request->get('address'))) $info['registeredAddress'] = $request->get('address');
		if (!empty($request->get('conn_address'))) $info['homePostalAddress'] = $request->get('conn_address');
		if (!empty($request->get('www'))) $info['wWWHomePage'] = $request->get('www');
		$openldap->createEntry($info);
		$entry = $openldap->getUserEntry($request->get('idno'));
		$json = $openldap->getUserData($entry);
        if ($json)
            return response()->json($json, 200, JSON_UNESCAPED_UNICODE);
        else
            return response()->json([ 'error' => '人員新增失敗：' . $openldap->error() ], 404);
    }
   
    public function peopleUpdate(Request $request, $uuid)
    {
        $openldap = new LdapServiceProvider();
        $dc = strtolower($request->get('o'));
		$entry = $openldap->getUserEntry($uuid);
		if (!$entry) return response()->json([ 'error' => '找不到指定的人員'], 404);
        $person = $openldap->getUserData($entry);
        $info = array();
        if (is_array($person['o']) && !in_array($dc, $person['o'])) {
            $orgs = $person['o'];
            $orgs[] = $dc;
            $info['o'] = $orgs;
            $educloud = array();
            foreach ($orgs as $o) {
                $entry = $openldap->getOrgEntry($o);
                $data = $openldap->getOrgData($entry, 'tpUniformNumbers');
                $sid = $data['tpUniformNumbers'];
                $educloud[] = json_encode(array("sid" => $sid, "role" => $request->get('type')), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
            }
            $info['info'] = $educloud;
        } elseif (!is_array($person['o']) && $person['o'] != $dc) {
            $info['o'] = $dc;
            $entry = $openldap->getOrgEntry($dc);
            $data = $openldap->getOrgData($entry, 'tpUniformNumbers');
            $sid = $data['tpUniformNumbers'];
            $info['info'] = json_encode(array("sid" => $sid, "role" => $request->get('type')), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        }
        if (!empty($request->get('lastname'))) $info['sn'] = $request->get('lastname');
		if (!empty($request->get('firstname'))) $info['givenName'] = $request->get('firstname');
		if (!empty($request->get('lastname')) && !empty($request->get('firstname'))) $info['displayName'] = $info['sn'].$info['givenName'];
        if ($person['employeeType'] != "學生") {
            if (!empty($request->get('unit'))) {
                $ous = array();
                $units = array();
                if (isset($person['ou'])) {
                    if (is_array($person['ou'])) {
                        $ous = $person['ou'];
                    } else {
                        $ous[] = $person['ou'];
                    }
                    foreach ($ous as $ou_pair) {
                        $a = explode(',', $ou_pair);
                        if (count($a) == 2 && $a[0] != $dc) $units[] = $ou_pair;
                    }
                }
                if (is_array($request->get('unit'))) {
                    $units = array_values($units + $request->get('unit'));
                } else {
                    $units[] = $request->get('unit');
                }
                $info['ou'] = $units;
            }
            if (!empty($request->get('role'))) {
                $titles = array();
                $roles = array();
                if (isset($person['title'])) {
                    if (is_array($person['title'])) {
                        $titles = $person['title'];
                    } else {
                        $titles[] = $person['title'];
                    }
                    foreach ($titles as $title_pair) {
                        $a = explode(',', $title_pair);
                        if (count($a) == 3 && $a[0] != $dc) $roles[] = $title_pair;
                    }
                }
                if (is_array($request->get('role'))) {
                    $roles = array_values($roles + $request->get('role'));
                } else {
                    $roles[] = $request->get('role');
                }
                $info['title'] = $roles;
            }
            if (!empty($request->get('tclass'))) {
                $assign = array();
                if (isset($person['tpTeachClass'])) {
                    if (is_array($person['tpTeachClass'])) {
                        $tclass = $person['tpTeachClass'];
                    } else {
                        $tclass[] = $person['tpTeachClass'];
                    }
                    foreach ($tclass as $pair) {
                        $a = explode(',', $pair);
                        if (count($a) == 3 && $a[0] != $dc) $assign[] = $pair;
                    }
                }
                if (is_array($request->get('tclass'))) {
                    $assign = array_values($assign + $request->get('tclass'));
                } else {
                    $assign[] = $request->get('tclass');
                }
                $info['tpTeachClass'] = $assign;
            }
        } else {
            if (!empty($request->get('stdno'))) $info['employeeNumber'] = $request->get('stdno');
            if (!empty($request->get('class'))) $info['tpClass'] = $request->get('class');
            if (!empty($request->get('seat'))) $info['tpSeat'] = $request->get('seat');
        }
		if (!empty($request->get('memo'))) $info['tpCharacter'] = $request->get('memo');
		if (!empty($request->get('gender'))) $info['gender'] = $request->get('gender');
		if (!empty($request->get('birthdate'))) $info['birthDate'] = $request->get('birthdate')."000000Z";
		if (!empty($request->get('email'))) $info['mail'] = $request->get('email');
		if (!empty($request->get('mobile'))) $info['mobile'] = $request->get('mobile');
		if (!empty($request->get('fax'))) $info['facsimileTelephoneNumber'] = $request->get('fax');
		if (!empty($request->get('otel'))) $info['telephoneNumber'] = $request->get('otel');
		if (!empty($request->get('htel'))) $info['homePhone'] = $request->get('htel');
		if (!empty($request->get('address'))) $info['registeredAddress'] = $request->get('address');
		if (!empty($request->get('conn_address'))) $info['homePostalAddress'] = $request->get('conn_address');
		if (!empty($request->get('www'))) $info['wWWHomePage'] = $request->get('www');
		$openldap->updateData($entry, $info);
        if (!empty($request->get('idno'))) {
            $idno = $request->get('idno');
            if ($person['cn'] != $idno) {
                $result = $openldap->renameUser($person['cn'], $idno);
                if ($result) {
                    $model = new \App\User();
                    $user = $model->newQuery()
                    ->where('idno', $person['cn'])
                    ->first();
                    if ($user) $user->delete();
                }
            }
        }
        $entry = $openldap->getUserEntry($uuid);
		$json = $openldap->getUserData($entry);
		return response()->json($json, 200, JSON_UNESCAPED_UNICODE);
    }

    public function peopleRemove(Request $request, $uuid)
    {
        $openldap = new LdapServiceProvider();
        $entry = $openldap->getUserEntry($uuid);
		if (!$entry) return response()->json([ 'error' => '找不到指定的人員'], 404);
        $result = $openldap->updateData($entry,  [ 'inetUserStatus' => 'deleted' ]);
//		$result = $openldap->deleteEntry($entry);
		if ($result)
		    return response()->json([ 'success' => '指定的人員已經刪除'], 410);
		else
		    return response()->json([ 'error' => '指定的人員刪除失敗：' . $openldap->error() ], 500);
    }
    
    public function people(Request $request, $uuid)
    {
        $openldap = new LdapServiceProvider();
        $entry = $openldap->getUserEntry($uuid);
		if (!$entry) return response()->json([ 'error' => '找不到指定的人員'], 404);
		$json = $openldap->getUserData($entry);
        if ($json)
            return response()->json($json, 200, JSON_UNESCAPED_UNICODE);
        else
            return response()->json([ 'error' => '找不到指定的人員'], 404);
    }

}
