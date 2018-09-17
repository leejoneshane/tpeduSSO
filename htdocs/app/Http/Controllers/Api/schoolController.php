<?php

namespace App\Http\Controllers\Api;

use Log;
use Auth;
use Config;
use App\Providers\LdapServiceProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class schoolController extends Controller
{
    public function all()
    {
		$openldap = new LdapServiceProvider();
		$json = $openldap->getOrgs();
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }
    
    public function one($dc)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$json = $openldap->getOrgData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allOu($dc)
    {
		$openldap = new LdapServiceProvider();
		$json = $openldap->getOus($dc, "行政部門");
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function oneOu($dc, $ou_id)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOuEntry($dc,$ou_id);
		$json = $openldap->getOuData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allSubject($dc)
    {
		$openldap = new LdapServiceProvider();
		$json = $openldap->getSubjects($dc);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function oneSubject($dc, $subj_id)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getSubjectEntry($dc,$subj_id);
		$json = $openldap->getSubjectData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allTeachersBySubject($dc, $subj_id)
    {
		$json = array();
		$openldap = new LdapServiceProvider();
		$teachers = $openldap->findUsers("(&(o=$dc)(tpTeachClass=*$subj_id))", "entryUUID");
		foreach ($teachers as $teacher) {
	    	$json[] = $teacher['entryUUID'];
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allClassesBySubject($dc, $subj_id)
    {
        $json = array();
        $classes = array();
		$openldap = new LdapServiceProvider();
		$teachers = $openldap->findUsers("(&(o=$dc)(tpTeachClass=*$subj_id))", ["o", "tpTeachClass"]);
		foreach ($teachers as $teacher) {
            if (is_array($teacher['tpTeachClass'])) {
                $classes = $teacher['tpTeachClass'];
            } else {
                $classes[] = $teacher['tpTeachClass'];
            }
            foreach ($classes as $class) {
                $a = explode(',', $class);
                if (count($a) == 3 && $a[0] == $dc && $a[2] == $subj_id && !in_array($a[1], $json)) $json[] = $a[1];
                if (count($a) == 2 && $a[1] == $subj_id && !in_array($a[0], $json)) $json[] = $a[0];
            }
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allRole($dc, $ou_id)
    {
		$openldap = new LdapServiceProvider();
		$json = $openldap->getRoles($dc,$ou_id);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function oneRole($dc, $ou_id, $role_id)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getRoleEntry($dc,$ou_id,$role_id);
		$json = $openldap->getRoleData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allClass($dc)
    {
		$openldap = new LdapServiceProvider();
		$json = $openldap->getOus($dc, "教學班級");
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function oneClass($dc, $class_id)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOuEntry($dc,$class_id);
		$json = $openldap->getOuData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allTeachersByClass($dc, $class_id)
    {
		$json = array();
		$openldap = new LdapServiceProvider();
		$teachers = $openldap->findUsers("(&(o=$dc)(tpTeachClass=*$class_id*))", "entryUUID");
		foreach ($teachers as $teacher) {
	    	$json[] = $teacher['entryUUID'];
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allStudentsByClass($dc, $class_id)
    {
		$json = array();
		$openldap = new LdapServiceProvider();
		$students = $openldap->findUsers("(&(o=$dc)(tpClass=$class_id))", "entryUUID");
		foreach ($students as $student) {
	    	$json[] = $student['entryUUID'];
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allSubjectsByClass($dc, $class_id)
    {
        $json = array();
        $classes = array();
		$openldap = new LdapServiceProvider();
		$teachers = $openldap->findUsers("(&(o=$dc)(tpTeachClass=*$class_id*))", ["o", "tpTeachClass"]);
		foreach ($teachers as $teacher) {
            if (is_array($teacher['tpTeachClass'])) {
                $classes = $teacher['tpTeachClass'];
            } else {
                $classes[] = $teacher['tpTeachClass'];
            }
            foreach ($classes as $class) {
                $a = explode(',', $class);
                if (count($a) == 3 && $a[0] == $dc && $a[1] == $class_id && !in_array($a[2], $json)) $json[] = $a[2];
                if (count($a) == 2 && $a[0] == $class_id && !in_array($a[1], $json)) $json[] = $a[1];
            }
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    // below function for schoolAdmin scope.
    public function updateSchool(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$user = $request->user();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, 'tpAdministrator');
		if (is_array($data['tpAdministrator'])) {
            if (!in_array($user->idno, $data['tpAdministrator']))
                return response()->json(["error" => "The user has no right to manager this school!"], 403);
		} elseif ($user->idno != $data['tpAdministrator']) {
		    return response()->json(["error" => "The user has no right to manager this school!"], 403);
		}
        $schoolinfo = array();
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
		$openldap->updateOus($request->get('ous'));
		$openldap->updateClasses($request->get('classes'));
		$openldap->updateSubjects($request->get('subjects'));
		$openldap->updateData($entry, $schoolinfo);
		$entry = $openldap->getOrgEntry($dc);
		$json = $openldap->getOrgData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function peopleAdd(Request $request, $dc)
    {
		$user = $request->user();
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, 'tpAdministrator');
		if (is_array($data['tpAdministrator'])) {
		    if (!in_array($user->idno, $data['tpAdministrator']))
				return response()->json(["error" => "The user has no right to manager this school!"], 403);
		} elseif ($user->idno != $data['tpAdministrator']) {
		    return response()->json(["error" => "The user has no right to manager this school!"], 403);
        }
        $idno = strtoupper($request->get('idno'));
        if (empty(idno)) return response()->json(["error" => "please provide user's ID Number!"], 400);
        $entry = $openldap->getUserEntry($idno);
        if ($entry) return response()->json(["error" => "The user was exists already!"], 400);
        if (empty($request->get('type'))) return response()->json(["error" => "please provide user's Role Type!"], 400);
        if (empty($request->get('lastname')) || empty($request->get('firstname'))) return response()->json(["error" => "please provide user's Real Name!"], 400);
        if (empty($request->get('school'))) return response()->json(["error" => "please provide user's Organization!"], 400);
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
        $orgs[] = $dc;
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
            if (empty($request->get('stdno'))) return response()->json(["error" => "please provide user's Student Number!"], 400);
            $info['employeeNumber'] = $request->get('stdno');
            if (empty($request->get('class'))) return response()->json(["error" => "please provide user's Study Class!"], 400);
            $info['tpClass'] = $request->get('class');
            if (empty($request->get('seat'))) return response()->json(["error" => "please provide user's Classroom Seat Number!"], 400);
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
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function peopleUpdate(Request $request, $dc, $uuid)
    {
		$user = $request->user();
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, 'tpAdministrator');
		if (is_array($data['tpAdministrator'])) {
		    if (!in_array($user->idno, $data['tpAdministrator']))
				return response()->json(["error" => "The user has no right to manager this school!"], 403);
		} elseif ($user->idno != $data['tpAdministrator']) {
		    return response()->json(["error" => "The user has no right to manager this school!"], 403);
        }
        $entry = $openldap->getUserEntry($uuid);
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
                        $titles[] = $original['title'];
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
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }
    
    public function peopleRemove(Request $request, $dc, $uuid)
    {
		$user = $request->user();
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, 'tpAdministrator');
		if (is_array($data['tpAdministrator'])) {
		    if (!in_array($user->idno, $data['tpAdministrator']))
				return response()->json(["error" => "The user has no right to manager this school!"], 403);
		} elseif ($user->idno != $data['tpAdministrator']) {
		    return response()->json(["error" => "The user has no right to manager this school!"], 403);
		}
		$entry = $openldap->getUserEntry($uuid);
		$result = $openldap->deleteEntry($entry);
		if ($result)
		    return response()->json([ 'success' => 'The people has been deleted!'], 410);
		else
		    return response()->json([ 'error' => 'The people can not delete!'], 500);
    }
    
    public function people(Request $request, $dc, $uuid)
    {
		$user = $request->user();
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, 'tpAdministrator');
		if (is_array($data['tpAdministrator'])) {
		    if (!in_array($user->idno, $data['tpAdministrator']))
				return response()->json(["error" => "The user has no right to manager this school!"], 403);
		} elseif ($user->idno != $data['tpAdministrator']) {
		    return response()->json(["error" => "The user has no right to manager this school!"], 403);
		}
		$entry = $openldap->getUserEntry($uuid);
		$json = $openldap->getUserData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    // below function for ajax calling
    public function listOrgs($area = '')
    {
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOrgs();
		$orgs = array();
		if (is_array($data)) {
			foreach ($data as $org) {
				if (empty($area) || isset($org->st) && $area == $org->st) $orgs[] = $org;
			}
		}
		return json_encode($orgs, JSON_UNESCAPED_UNICODE);
    }
    
    public function listClasses($dc, $grade = '')
    {
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, "教學班級");
		$classes = array();
		foreach ($data as $class) {
			if (empty($grade) || $grade == substr($class->ou, 0, 1)) $classes[] = $class;
		}
		return json_encode($classes, JSON_UNESCAPED_UNICODE);
    }

    public function listTeachers($dc, $ou)
    {
		$json = array();
		$openldap = new LdapServiceProvider();
		$teachers = $openldap->findUsers("(&(o=$dc)(ou=*$ou))", ["cn","displayName","o","ou","title"]);
		foreach ($teachers as $one) {
			$teacher = new \stdClass;
			$teacher->idno = $one['cn'];
            $teacher->name = $one['displayName'];           
            if (isset($one['titleName'][$dc])) {
                foreach ($one['titleName'][$dc] as $role_pair => $title) {
                    $a = explode(',', $role_pair);
                    if (count($a) == 3 && $a[0] == $dc && $a[1] == $ou) $teacher->title = $title;
                }
                if (!isset($teacher->title)) $teacher->title = array_values($one['titleName'][$dc])[0];
            }

			$json[] = $teacher;
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }
}
