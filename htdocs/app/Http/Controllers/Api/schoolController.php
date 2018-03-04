<?php

namespace App\Http\Controllers\Api;

use Log;
use Auth;
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
    
    public function one($sid)
    {
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOrgEntry($sid);
	$json = $openldap->getOrgData($entry);
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allOu($sid)
    {
	$openldap = new LdapServiceProvider();
	$json = $openldap->getOus($sid, "行政部門");
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function oneOu($sid, $ou_id)
    {
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOuEntry($sid,$ou_id);
	$json = $openldap->getOuData($entry);
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allRole($sid, $ou_id)
    {
	$openldap = new LdapServiceProvider();
	$json = $openldap->getRoles($sid,$ou_id);
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function oneRole($sid, $ou_id, $role_id)
    {
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getRoleEntry($sid,$ou_id,$role_id);
	$json = $openldap->getRoleData($entry);
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allClass($sid)
    {
	$openldap = new LdapServiceProvider();
	$json = $openldap->getOus($sid, "教學班級");
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function oneClass($sid, $class_id)
    {
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOuEntry($sid,$class_id);
	$json = $openldap->getOuData($entry);
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allTeachers($sid, $class_id)
    {
	$json = array();
	$openldap = new LdapServiceProvider();
	$teachers = $openldap->findUsers("(&(o=$sid)(tpTeachClass=$class_id))");
	for ($i=0;$i < $teachers['count'];$i++) {
	    $json[] = $teachers[$i]['entryuuid'][0];
	}
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function allStudents($sid, $class_id)
    {
	$json = array();
	$openldap = new LdapServiceProvider();
	$students = $openldap->findUsers("(&(o=$sid)(tpClass=$class_id))");
	for ($i=0;$i < $students['count'];$i++) {
	    $json[] = $students[$i]['entryuuid'][0];
	}
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function updateSchool(Request $request, $sid)
    {
	$user = $request->user();
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOrgEntry($sid);
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
	$schoolinfo['ous'] = $request->get('ous');
	$schoolinfo['roles'] = $request->get('roless');
	$schoolinfo['tpAdministrator'] = $request->get('admins');
	$openldap->updateData($entry, $schoolinfo);
	$json = $openldap->getOrgData($entry);
	return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    public function peopleAdd(Request $request, $sid)
    {
	$user = $request->user();
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOrgEntry($sid);
	$data = $openldap->getOrgData($entry, 'tpAdministrator');
	if (is_array($data['tpAdministrator'])) {
	    if (!in_array($user->idno, $data['tpAdministrator']))
		return response()->json(["error" => "The user has no right to manager this school!"], 403);
	} elseif ($user->idno != $data['tpAdministrator']) {
	    return response()->json(["error" => "The user has no right to manager this school!"], 403);
	}

    }

    public function peopleUpdate(Request $request, $sid, $uuid)
    {
	$user = $request->user();
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOrgEntry($sid);
	$data = $openldap->getOrgData($entry, 'tpAdministrator');
	if (is_array($data['tpAdministrator'])) {
	    if (!in_array($user->idno, $data['tpAdministrator']))
		return response()->json(["error" => "The user has no right to manager this school!"], 403);
	} elseif ($user->idno != $data['tpAdministrator']) {
	    return response()->json(["error" => "The user has no right to manager this school!"], 403);
	}

    }
    
    public function peopleRemove(Request $request, $sid, $uuid)
    {
	$user = $request->user();
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOrgEntry($sid);
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
    
    public function people(Request $request, $sid, $uuid)
    {
	$user = $request->user();
	$openldap = new LdapServiceProvider();
	$entry = $openldap->getOrgEntry($sid);
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
