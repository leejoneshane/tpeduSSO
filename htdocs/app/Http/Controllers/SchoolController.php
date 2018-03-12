<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;
use Log;

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
    
    public function schoolProfileForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry);
		return view('admin.schoolprofile', [ 'data' => $data, 'dc' => $dc ]);
    }

    public function updateSchoolProfile(Request $request)
    {
		$dc = $request->get('dc');
		$openldap = new LdapServiceProvider();
		$messages = '';
		$result = true;
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
			return redirect()->back()->with("error", "學校基本資料變更失敗！");
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
			return redirect()->back()->with("error", $messages);
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
				return redirect()->back()->with("error","管理員刪除失敗，請稍後再試一次！");
	    	}
		}
    }
    
}
