<?php

namespace App\Http\Controllers;

use Config;
use Illuminate\Http\Request;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;

class SchoolController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('school');
    }
    
    public function schoolTeacherSearchForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '行政部門');
		$my_ou = $data[0]->ou;
		$my_field = $request->get('field', "ou=$my_ou");
		$keywords = $request->get('keywords');
		$ous = array();
		foreach ($data as $ou) {
			if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
		}
		if (substr($my_field,0,3) == 'ou=') {
			$my_ou = substr($my_field,3);
			$filter = "(&(o=$dc)(ou=$my_ou)(employeeType=教師))";
		} elseif ($my_field == 'uuid' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=教師)(entryUUID=*".$keywords."*))";
		} elseif ($my_field == 'idno' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=教師)(cn=*".$keywords."*))";
		} elseif ($my_field == 'name' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=教師)(displayName=*".$keywords."*))";
		} elseif ($my_field == 'mail' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=教師)(mail=*".$keywords."*))";
		} elseif ($my_field == 'mobile' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=教師)(mobile=*".$keywords."*))";
		}
		$teachers = $openldap->findUsers($filter, ["cn","displayName","uid","o","ou","title","entryUUID"]);
		for ($i=0;$i<$teachers['count'];$i++) {
			$dc = $teachers[$i]['o'][0];
			$teachers[$i]['school']['count'] = 1;
			$teachers[$i]['school'][0] = $openldap->getOrgTitle($dc);
			if ($teachers[$i]['ou']['count']>0)  {
				$ou = $teachers[$i]['ou'][0];
				$teachers[$i]['department']['count'] = 1;
				$teachers[$i]['department'][0] = $openldap->getOuTitle($dc, $ou);
				if ($teachers[$i]['title']['count']>0)  {
					$role = $teachers[$i]['title'][0];
					$teachers[$i]['titlename']['count'] = 1;
					$teachers[$i]['titlename'][0] = $openldap->getRoleTitle($dc, $ou, $role);
				}
			}
		}
		return view('admin.schoolteacher', [ 'my_field' => $my_field, 'keywords' => $keywords, 'ous' => $ous, 'teachers' => $teachers ]);
    }

    public function schoolTeacherJSONForm(Request $request)
    {
	}
	
    public function importSchoolTeacher(Request $request)
    {
	}
	
    public function schoolTeacherEditForm(Request $request, $uuid)
    {
	}
	
    public function createSchoolTeacher(Request $request)
    {
	}
	
    public function updateSchoolTeacher(Request $request, $uuid)
    {
	}
	
    public function removeSchoolTeacher(Request $request, $uuid)
    {
	}
	
    public function schoolRoleForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '行政部門');
		$my_ou = $request->get('ou', $data[0]->ou);
		$ous = array();
		foreach ($data as $ou) {
			if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
		}
		$roles = $openldap->getRoles($dc, $my_ou);
		return view('admin.schoolrole', [ 'my_ou' => $my_ou, 'ous' => $ous, 'roles' => $roles ]);
    }

    public function createSchoolRole(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$ou = $request->get('ou');
		$validatedData = $request->validate([
			'new-role' => 'required|string',
			'new-desc' => 'required|string',
		]);
		$info = array();
		$info['objectClass'] = 'organizationalRole';
		$info['cn'] = $request->get('new-role');
		$info['ou'] = $ou;
		$info['description'] = $request->get('new-desc');
		$info['dn'] = "cn=".$info['cn'].",ou=$ou,dc=$dc,".Config::get('ldap.rdn');
		$openldap = new LdapServiceProvider();
		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您建立職務！");
		} else {
			return redirect()->back()->with("error", "職務建立失敗！".$openldap->error());
		}
    }

    public function updateSchoolRole(Request $request, $role)
    {
		$dc = $request->user()->ldap['o'];
		$ou = $request->get('ou');
		$validatedData = $request->validate([
			'role' => 'required|string',
			'description' => 'required|string',
		]);
		$info = array();
		$info['cn'] = $request->get('role');
		$info['description'] = $request->get('description');
		
		$openldap = new LdapServiceProvider();
		if ($role != $info['role']) {
			$users = $openldap->findUsers("(&(o=$dc)(ou=$ou)(title=$role))", "cn");
			for ($i=0;$i < $users['count'];$i++) {
	    		$idno = $students[$i]['cn'][0];
	    		$user_entry = $openldap->getUserEntry($idno);
	    		$openldap->updateData($user_entry, ['title' => $info['cn'] ]);
			}
		}
		$entry = $openldap->getRoleEntry($dc, $ou, $role);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您更新職務資訊！");
		} else {
			return redirect()->back()->with("error", "職務資訊更新失敗！".$openldap->error());
		}
    }

    public function removeSchoolRole(Request $request, $role)
    {
		$dc = $request->user()->ldap['o'];
		$ou = $request->get('ou');
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(ou=$ou)(title=$role))", "cn");
		if ($users && $users['count']>0) {
			return redirect()->back()->with("error", "尚有人員從事該職務，因此無法刪除！");
		}
		$entry = $openldap->getRoleEntry($dc, $ou, $role);
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return redirect()->back()->with("success", "已經為您移除職務！");
		} else {
			return redirect()->back()->with("error", "職務刪除失敗！".$openldap->error());
		}
    }

    public function schoolClassForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$my_grade = $request->get('grade', 1);
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '教學班級');
		$grades = array();
		$classes = array();
		foreach ($data as $class) {
			$grade = substr($class->ou, 0, 1);
			if (!in_array($grade, $grades)) $grades[] = $grade;
			if ($grade == $my_grade) $classes[] = $class;
		}
		return view('admin.schoolclass', [ 'my_grade' => $my_grade, 'grades' => $grades, 'classes' => $classes ]);
    }

    public function createSchoolClass(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$validatedData = $request->validate([
			'new-ou' => 'required|digits:3',
			'new-desc' => 'required|string',
		]);
		$info = array();
		$info['objectClass'] = 'organizationalUnit';
		$info['businessCategory']='教學班級'; //右列選一:行政部門,教學領域,教師社群或社團,學生社團或營隊
		$info['ou'] = $request->get('new-ou');
		$info['description'] = $request->get('new-desc');
		$info['dn'] = "ou=".$info['ou'].",dc=$dc,".Config::get('ldap.rdn');
		$openldap = new LdapServiceProvider();
		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您建立班級！");
		} else {
			return redirect()->back()->with("error", "班級建立失敗！".$openldap->error());
		}
	}
	
    public function updateSchoolClass(Request $request, $class)
    {
		$dc = $request->user()->ldap['o'];
		$validatedData = $request->validate([
			'description' => 'required|string',
		]);
		$info = array();
		$info['description'] = $request->get('description');
		
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(tpClass=$class))", "cn");
		for ($i=0;$i < $users['count'];$i++) {
	    	$idno = $students[$i]['cn'][0];
	    	$user_entry = $openldap->getUserEntry($idno);
	    	$openldap->updateData($user_entry, ['tpClassTitle' => $info['description'] ]);
		}
		$entry = $openldap->getOUEntry($dc, $class);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您更新班級資訊！");
		} else {
			return redirect()->back()->with("error", "班級資訊更新失敗！".$openldap->error());
		}
	}
	
    public function removeSchoolClass(Request $request, $class)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(|(tpClass=$class)(tpTeachClass=$class)))", "cn");
		if ($users && $users['count']>0) {
			return redirect()->back()->with("error", "尚有人員隸屬於該行政部門，因此無法刪除！");
		}
		$entry = $openldap->getOUEntry($dc, $class);
		$roles = $openldap->getRoles($dc, $class);
		foreach ($roles as $role) {
			$role_entry = $openldap->getRoleEntry($dc, $class, $role->cn);
			$openldap->deleteEntry($role_entry);
		}
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return redirect()->back()->with("success", "已經為您移除班級！");
		} else {
			return redirect()->back()->with("error", "班級刪除失敗！".$openldap->error());
		}
	}
	
    public function schoolUnitForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '行政部門');
		return view('admin.schoolunit', [ 'ous' => $data ]);
    }

    public function createSchoolUnit(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$validatedData = $request->validate([
			'new-ou' => 'required|string',
			'new-desc' => 'required|string',
		]);
		$info = array();
		$info['objectClass'] = 'organizationalUnit';
		$info['businessCategory']='行政部門'; //右列選一:行政部門,教學領域,教師社群或社團,學生社團或營隊
		$info['ou'] = $request->get('new-ou');
		$info['description'] = $request->get('new-desc');
		$info['dn'] = "ou=".$info['ou'].",dc=$dc,".Config::get('ldap.rdn');
		$openldap = new LdapServiceProvider();
		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您建立行政部門！");
		} else {
			return redirect()->back()->with("error", "行政部門建立失敗！".$openldap->error());
		}
    }

    public function updateSchoolUnit(Request $request, $ou)
    {
		$dc = $request->user()->ldap['o'];
		$validatedData = $request->validate([
			'ou' => 'required|string',
			'description' => 'required|string',
		]);
		$info = array();
		$info['ou'] = $request->get('ou');
		$info['description'] = $request->get('description');
		
		$openldap = new LdapServiceProvider();
		if ($ou != $info['ou']) {
			$users = $openldap->findUsers("(&(o=$dc)(ou=$ou))", "cn");
			for ($i=0;$i < $users['count'];$i++) {
	    		$idno = $students[$i]['cn'][0];
	    		$user_entry = $openldap->getUserEntry($idno);
	    		$openldap->updateData($user_entry, ['ou' => $info['ou'] ]);
			}
		}
		$entry = $openldap->getOUEntry($dc, $ou);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您更新行政部門資訊！");
		} else {
			return redirect()->back()->with("error", "行政部門資訊更新失敗！".$openldap->error());
		}
    }

    public function removeSchoolUnit(Request $request, $ou)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(ou=$ou))", "cn");
		if ($users && $users['count']>0) {
			return redirect()->back()->with("error", "尚有人員隸屬於該行政部門，因此無法刪除！");
		}
		$entry = $openldap->getOUEntry($dc, $ou);
		$roles = $openldap->getRoles($dc, $ou);
		foreach ($roles as $role) {
			$role_entry = $openldap->getRoleEntry($dc, $ou, $role->cn);
			$openldap->deleteEntry($role_entry);
		}
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return redirect()->back()->with("success", "已經為您移除行政部門！");
		} else {
			return redirect()->back()->with("error", "行政部門刪除失敗！".$openldap->error());
		}
    }

    public function schoolProfileForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry);
		return view('admin.schoolprofile', [ 'data' => $data ]);
    }

    public function updateSchoolProfile(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$validatedData = $request->validate([
			'description' => 'required|string',
			'businessCategory' => 'required|string',
			'st' => 'required|string',
			'fax' => 'nullable|string',
			'telephoneNumber' => 'required|string',
			'postalCode' => 'required|digits_between:3,5',
			'street' => 'required|string',
			'postOfficeBox' => 'required|digits:3',
			'wWWHomePage' => 'nullable|url',
			'tpUniformNumbers' => 'required|digits:6',
			'tpIpv4' => new ipv4cidr,
			'tpIpv6' => new ipv6cidr,
		]);
		$info = array();
		$info['description'] = $request->get('description');
		$info['businessCategory'] = $request->get('businessCategory');
		$info['st'] = $request->get('st');
		if ($request->has('fax')) $info['fax'] = $request->get('fax');
		$info['telephoneNumber'] = $request->get('telephoneNumber');
		$info['postalCode'] = $request->get('postalCode');
		$info['street'] = $request->get('street');
		$info['postOfficeBox'] = $request->get('postOfficeBox');
		if ($request->has('wWWHomePage')) $info['wWWHomePage'] = $request->get('wWWHomePage');
		$info['tpUniformNumbers'] = $request->get('tpUniformNumbers');
		$info['tpIpv4'] = $request->get('tpIpv4');
		$info['tpIpv6'] = $request->get('tpIpv6');
	
		$entry = $openldap->getOrgEntry($dc);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您更新學校基本資料！");
		} else {
			return redirect()->back()->with("error", "學校基本資料變更失敗！".$openldap->error());
		}
    }

    public function schoolAdminForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, "tpAdministrator");
		if (array_key_exists('tpAdministrator', $data)) {
		    if (is_array($data['tpAdministrator'])) 
				$admins = $data['tpAdministrator'];
		    else 
				$admins[] = $data['tpAdministrator'];
		} else {
		    $admins = array();
		}
		return view('admin.schooladminwithsidebar', [ 'admins' => $admins, 'dc' => $dc ]);
    }

    public function showSchoolAdminSettingForm(Request $request)
    {
		if ($request->session()->has('dc')) {
		    $dc = $request->session()->get('dc');
		} else {
		    return redirect('/');
		}
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, "tpAdministrator");
		if (array_key_exists('tpAdministrator', $data)) {
		    if (is_array($data['tpAdministrator'])) 
				$admins = $data['tpAdministrator'];
		    else 
				$admins[] = $data['tpAdministrator'];
		} else {
		    $admins = array();
		}
		return view('admin.schooladmin', [ 'admins' => $admins, 'dc' => $dc ]);
    }

    public function addSchoolAdmin(Request $request)
    {
		$dc = $request->get('dc');
		$openldap = new LdapServiceProvider();
		$messages = '';
		$result1 = true;
		$result2 = true;
		if (!empty($request->get('new-admin'))) {
	    	$validatedData = $request->validate([
				'new-admin' => new idno,
			]);
		    $idno = Config::get('ldap.userattr')."=".$request->get('new-admin');
	    	$entry = $openldap->getUserEntry($request->get('new-admin'));
		    if ($entry) {
				$data = $openldap->getUserData($entry, "o");
				if (isset($data['o']) && $data['o'] != $dc) {
		    		return redirect()->back()->with("error","該使用者並不隸屬於貴校，無法設定為學校管理員！");
				}
		    } else {
				return redirect()->back()->with("error","您輸入的身分證字號，不存在於系統！");
	    	}
	    
		    $entry = $openldap->getOrgEntry($dc);
		    $result1 = $openldap->addData($entry, [ 'tpAdministrator' => $request->get('new-admin')]);
	    	if ($result1) {
				$messages = "已經為您新增學校管理員！";
		    } else {
				$messages = "管理員無法新增到資料庫，請檢查管理員是否重複設定！";
	    	}
		}
		if (!empty($request->get('new-password'))) {
	    	$validatedData = $request->validate([
				'new-password' => 'required|string|min:6|confirmed',
			]);
		    $entry = $openldap->getOrgEntry($dc);
		    $ssha = $openldap->make_ssha_password($request->get('new-password'));
	    	$result2 = $openldap->updateData($entry, array('userPassword' => $ssha));
		    if ($result2) {
				$messages .= "密碼已經變更完成！";
	    	} else {
				$messages .= "密碼無法寫入資料庫，請稍後再試一次！";
		    }
		}
		if ($result1 && $result2) {
			return redirect()->back()->with("success", $messages);
		} else {
			return redirect()->back()->with("error", $messages.$openldap->error());
		}
    }
    
    public function delSchoolAdmin(Request $request)
    {
		$dc = $request->get('dc');
		$openldap = new LdapServiceProvider();
		if ($request->has('delete-admin')) {
		    $entry = $openldap->getOrgEntry($dc);
		    $result = $openldap->deleteData($entry, [ 'tpAdministrator' => $request->get('delete-admin')]);
	    	if ($result) {
				return redirect()->back()->with("success","已經為您刪除學校管理員！");
		    } else {
				return redirect()->back()->with("error","管理員刪除失敗，請稍後再試一次！".$openldap->error());
	    	}
		}
    }
    
}
