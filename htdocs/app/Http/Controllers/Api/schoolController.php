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

    public function listClasses($dc, $grade)
    {
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, "教學班級");
		$classes = array();
		foreach ($data as $class) {
			if ($grade == substr($class->ou, 0, 1)) $classes[] = $class;
		}
		return json_encode($classes, JSON_UNESCAPED_UNICODE);
    }

    public function oneClass($dc, $class_id)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOuEntry($dc,$class_id);
		$json = $openldap->getOuData($entry);
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function listTeachers($dc, $ou)
    {
		$json = array();
		$openldap = new LdapServiceProvider();
		$data = $openldap->findUsers("(&(o=$dc)(ou=$ou))", ["cn","displayName","o","ou","title"]);
		for ($i=0;$i<$data['count'];$i++) {
			$teacher = new \stdClass;
			$teacher->idno = $data[$i]['cn'][0];
			$teacher->name = $data[$i]['displayname'][0];
			$teacher->title = $openldap->getRoleTitle($dc, $data[$i]['ou'][0], $data[$i]['title'][0]);
			$json[] = $teacher;
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allTeachers($dc, $class_id)
    {
		$json = array();
		$openldap = new LdapServiceProvider();
		$teachers = $openldap->findUsers("(&(o=$dc)(tpTeachClass=$class_id))");
		for ($i=0;$i < $teachers['count'];$i++) {
	    	$json[] = $teachers[$i]['entryuuid'][0];
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allStudents($dc, $class_id)
    {
		$json = array();
		$openldap = new LdapServiceProvider();
		$students = $openldap->findUsers("(&(o=$dc)(tpClass=$class_id))");
		for ($i=0;$i < $students['count'];$i++) {
	    	$json[] = $students[$i]['entryuuid'][0];
		}
		return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function updateSchool(Request $request, $dc)
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
		$schoolinfo = array();
		$schoolinfo['cn'] = $request->get('cn');
		$schoolinfo['businessCatagory'] = $request->get('type');
		$schoolinfo['st'] = $request->get('area');
		$schoolinfo['description'] = $request->get('name');
		$schoolinfo['facsimileTelephoneNumber'] = $request->get('fax');
		$schoolinfo['telephoneNumber'] = $request->get('tel');
		$schoolinfo['postalCode'] = $request->get('postal');
		$schoolinfo['street'] = $request->get('address');
		$schoolinfo['postOfficeBox'] = $request->get('mbox');
		$schoolinfo['wWWHomePage'] = $request->get('www');
		$schoolinfo['tpUniformNumbers'] = $request->get('uno');
		$schoolinfo['tpIpv4'] = $request->get('ipv4');
		$schoolinfo['tpIpv6'] = $request->get('ipv6');
		$schoolinfo['subjects'] = $request->get('subjects');
		$schoolinfo['ous'] = $request->get('ous');
		$schoolinfo['roles'] = $request->get('roles');
		$schoolinfo['tpAdministrator'] = $request->get('admins');
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
		$info = array();
		$info['dn'] = "cn=".$request->get('idno').",".Config::get('ldap.userdn');
		$info["objectClass"] = array("tpeduPerson","inetUser");
 		$info["inetUserStatus"] = "Active";
    	$info["cn"] = $request->get('idno');
	    $info["userPassword"] = $openldap->make_ssha_password($request->get('password'));
		$info['o'] = $request->get('school');
		$info['ou'] = $request->get('unit');
		$info['title'] = $request->get('role');
		$info['tpTeachClass'] = $request->get('tclass');
		$info['tpCharacter'] = $request->get('memo');
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpClass'] = $request->get('class');
		$info['tpSeat'] = $request->get('seat');
		$info['info'] = $request->get('info');
		$info['employeeType'] = $request->get('type');
		$info['sn'] = $request->get('lastname');
		$info['givenName'] = $request->get('firstname');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = $request->get('gender');
		$info['birthDate'] = $request->get('birthdate');
		$info['mail'] = $request->get('email');
		$info['mobile'] = $request->get('mobile');
		$info['facsimileTelephoneNumber'] = $request->get('fax');
		$info['telephoneNumber'] = $request->get('otel');
		$info['homePhone'] = $request->get('htel');
		$info['registeredAddress'] = $request->get('address');
		$info['homePostalAddress'] = $request->get('conn_address');
		$info['wWWHomePage'] = $request->get('www');
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
		$info = array();
		$info['o'] = $request->get('school');
		$info['ou'] = $request->get('unit');
		$info['title'] = $request->get('role');
		$info['tpTeachClass'] = $request->get('tclass');
		$info['tpCharacter'] = $request->get('memo');
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpClass'] = $request->get('class');
		$info['tpSeat'] = $request->get('seat');
		$info['info'] = $request->get('info');
		$info['employeeType'] = $request->get('type');
		$info['sn'] = $request->get('lastname');
		$info['givenName'] = $request->get('firstname');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = $request->get('gender');
		$info['birthDate'] = $request->get('birthdate');
		$info['mail'] = $request->get('email');
		$info['mobile'] = $request->get('mobile');
		$info['facsimileTelephoneNumber'] = $request->get('fax');
		$info['telephoneNumber'] = $request->get('otel');
		$info['homePhone'] = $request->get('htel');
		$info['registeredAddress'] = $request->get('address');
		$info['homePostalAddress'] = $request->get('conn_address');
		$info['wWWHomePage'] = $request->get('www');
		$entry = $openldap->getUserEntry($uuid);
		$openldap->updateData($entry, $info);
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
    
}
