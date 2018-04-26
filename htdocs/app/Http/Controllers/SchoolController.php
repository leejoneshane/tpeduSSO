<?php

namespace App\Http\Controllers;

use Config;
use Validator;
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
    
    public function schoolStudentSearchForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '教學班級');
		$my_ou = $data[0]->ou;
		$my_field = $request->get('field', "ou=$my_ou");
		$keywords = $request->get('keywords');
		$request->session()->put('field', $my_field);
		$request->session()->put('keywords', $keywords);
		$ous = array();
		foreach ($data as $ou) {
			if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
		}
		if (substr($my_field,0,3) == 'ou=') {
			$my_ou = substr($my_field,3);
			if ($my_ou == 'deleted')
				$filter = "(&(o=$dc)(inetUserStatus=deleted)(employeeType=學生))";
			else
				$filter = "(&(o=$dc)(tpClass=$my_ou)(employeeType=學生)(!(inetUserStatus=deleted)))";
		} elseif ($my_field == 'uuid' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=學生)(entryUUID=*".$keywords."*))";
		} elseif ($my_field == 'idno' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=學生)(cn=*".$keywords."*))";
		} elseif ($my_field == 'name' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=學生)(displayName=*".$keywords."*))";
		} elseif ($my_field == 'mail' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=學生)(mail=*".$keywords."*))";
		} elseif ($my_field == 'mobile' && !empty($keywords)) {
			$filter = "(&(o=$dc)(employeeType=學生)(mobile=*".$keywords."*))";
		}
		$students = $openldap->findUsers($filter, ["cn","displayName","tpClass","tpSeat","entryUUID","inetUserStatus"]);
		for ($i=0;$i<$students['count'];$i++) {
			if (!array_key_exists('inetuserstatus', $students[$i]) || $students[$i]['inetuserstatus']['count'] == 0) {
				$students[$i]['inetuserstatus']['count'] = 1;
				$students[$i]['inetuserstatus'][0] = '啟用';
			} elseif (strtolower($students[$i]['inetuserstatus'][0]) == 'active') {
				$students[$i]['inetuserstatus'][0] = '啟用';
			} elseif (strtolower($students[$i]['inetuserstatus'][0]) == 'inactive') {
				$students[$i]['inetuserstatus'][0] = '停用';
			} elseif (strtolower($students[$i]['inetuserstatus'][0]) == 'deleted') {
				$students[$i]['inetuserstatus'][0] = '已刪除';
			}
		}
		return view('admin.schoolstudent', [ 'my_field' => $my_field, 'keywords' => $keywords, 'classes' => $ous, 'students' => $students ]);
    }

    public function schoolStudentJSONForm(Request $request)
	{
		$dc = $request->user()->ldap['o'];
		$user = new \stdClass;
		$user->id = 'B123456789';
		$user->account = 'myaccount';
		$user->password = 'My_p@ssw0rD';
		$user->stdno = '102247';
		$user->class = '601';
		$user->seat = '7';
		$user->character = '雙胞胎 外籍配偶子女';
		$user->sn = '蘇';
		$user->gn = '小小';
		$user->name = '蘇小小';
		$user->gender = 2;
		$user->birthdate = '20101105';
		$user->mail = 'johnny@tp.edu.tw';
		$user->mobile = '0900100200';
		$user->fax = '(02)23093736';
		$user->otel = '(02)23033555';
		$user->htel = '(03)3127221';
		$user->register = "臺北市中正區龍興里9鄰三元街17巷22號5樓";
		$user->address = "新北市板橋區中山路1段196號";
		$user->www = 'http://johnny.dev.io';
		$user2 = new \stdClass;
		$user2->id = 'B123456789';
		$user2->stdno = '102247';
		$user2->class = '601';
		$user2->seat = '7';
		$user2->sn = '蘇';
		$user2->gn = '小小';
		$user2->gender = 2;
		$user2->birthdate = '20101105';
		return view('admin.schoolstudentjson', [ 'dc' => $dc, 'sample1' => $user, 'sample2' => $user2 ]);
	}

    public function importSchoolStudent(Request $request)
	{
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$sid = $openldap->getOrgData($entry, 'tpUniformNumbers');
		$sid = $sid['tpUniformNumbers'];
    	$messages[0] = "heading";
    	if ($request->hasFile('json')) {
	    	$path = $request->file('json')->path();
    		$content = file_get_contents($path);
    		$json = json_decode($content);
    		if (!$json)
				return redirect()->back()->with("error", "檔案剖析失敗，請檢查 JSON 格式是否正確？");
			$rule = new idno;
			$students = array();
			if (is_array($json)) { //批量匯入
				$students = $json;
			} else {
				$students[] = $json;
			}
			$i = 0;
	 		foreach($students as $person) {
				$i++;
				if (!isset($person->name) || empty($person->name)) {
					if (empty($person->sn) || empty($person->gn)) {
						$messages[] = "第 $i 筆記錄，無學生姓名，跳過不處理！";
		    			continue;
					}
					$person->name = $person->sn.$person->gn;
				}
				if (!isset($person->id) || empty($person->id)) {
					$messages[] = "第 $i 筆記錄，無身分證字號，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'idno' => $person->id ], [ 'idno' => new idno ]
    			);
				if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."身分證字號格式或內容不正確，跳過不處理！";
		    		continue;
				}
				if (!isset($person->stdno) || empty($person->stdno)) {
					$messages[] = "第 $i 筆記錄，".$person->name."無學號，跳過不處理！";
		    		continue;
				}
				if (!isset($person->class) || empty($person->class)) {
					$messages[] = "第 $i 筆記錄，".$person->name."無就讀班級，跳過不處理！";
		    		continue;
				}
				if (!isset($person->seat) || empty($person->seat)) {
					$messages[] = "第 $i 筆記錄，".$person->name."無座號，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'gender' => $person->gender ], [ 'gender' => 'required|digits:1' ]
    			);
    			if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."性別資訊不正確，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'date' => $person->birthdate ], [ 'date' => 'required|date' ]
				);
	    		if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."出生日期格式或內容不正確，跳過不處理！";
		    		continue;
				}
				$user_dn = Config::get('ldap.userattr')."=".$person->id.",".Config::get('ldap.userdn');
				$entry = array();
				$entry["objectClass"] = array("tpeduPerson","inetUser");
 				$entry["inetUserStatus"] = "active";
   				$entry["cn"] = $person->id;
    			$entry["sn"] = $person->sn;
    			$entry["givenName"] = $person->gn;
    			$entry["displayName"] = $person->name;
    			$entry["gender"] = $person->gender;
				$entry["birthDate"] = $person->birthdate."000000Z";
    			$entry["o"] = $dc;
    			$entry["employeeType"] = "學生";
				$entry['info'] = json_encode(array("sid" => $sid, "role" => "學生"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    			$entry["employeeNumber"] = $person->stdno;
    			$entry["tpClass"] = $person->class;
    			$entry["tpSeat"] = $person->seat;
				$account = array();
   				$account["objectClass"] = "radiusObjectProfile";
			    $account["cn"] = $person->id;
			    $account["description"] = '管理員匯入';
				if (isset($person->account) && !empty($person->account))
					$account["uid"] = $person->account;
				else
					$account["uid"] = $dc.$person->stdno;
    			$entry["uid"] = $account["uid"];
				if (isset($person->password) && !empty($person->password))
					$password = $openldap->make_ssha_password($person->password);
				else
					$password = $openldap->make_ssha_password(substr($person->id, -6));
	   			$account["userPassword"] = $password;
	   			$account_dn = Config::get('ldap.authattr')."=".$account['uid'].",".Config::get('ldap.authdn');
	   			$entry["userPassword"] = $password;
		    	if (isset($person->character) && !empty($person->character))
	    			$entry['tpCharacter'] = explode(' ', $person->character);
		    	if (isset($person->mail) && !empty($person->mail)) {
		    		$data = array();
		    		$mails = array();
		    		if (is_array($person->mail)) {
		    			$data = $person->mail;
		    		} else {
		    			$data[] = $person->mail;
		    		}
		    		foreach ($data as $mail) {
						$validator = Validator::make(
    						[ 'mail' => $mail ], [ 'mail' => 'email' ]
    					);
	    				if ($validator->passes()) $mails[] = $mail;
	    			}
	    			if (count($mails) > 0) $entry['mail'] = $mails;
    			}
			    if (isset($person->mobile) && !empty($person->mobile)) {
		    		$data = array();
		    		$mobiles = array();
			    	if (is_array($person->mobile)) {
			    		$data = $person->mobile;
			    	} else {
			    		$data[] = $person->mobile;
			    	}
			    	foreach ($data as $mobile) {
						$validator = Validator::make(
    						[ 'mobile' => $mobile ], [ 'mobile' => 'digits:10' ]
    					);
		    			if ($validator->passes()) $mobiles[] = $mobile;
					}
	   				if (count($mobiles) > 0) $entry['mobile'] = $mobiles;
    			}
			    if (isset($person->fax) && !empty($person->fax)) {
			    	$data = array();
			    	$fax = array();
			    	if (is_array($person->fax)) {
			    		$data = $person->fax;
			    	} else {
			    		$data[] = $person->fax;
			    	}
				    foreach ($data as $tel) {
				    	$fax[] = self::convert_tel($tel);
  					}
		    		if (count($fax) > 0) $entry['facsimileTelephoneNumber'] = $fax;
    			}
			    if (isset($person->otel) && !empty($person->otel)) {
			    	$data = array();
			    	$otel = array();
			    	if (is_array($person->otel)) {
			    		$data = $person->otel;
			    	} else {
			    		$data[] = $person->otel;
			    	}
				    foreach ($data as $tel) {
				    	$otel[] = self::convert_tel($tel);
  					}
		    		if (count($otel) > 0) $entry['telephoneNumber'] = $otel;
    			}
			    if (isset($person->htel) && !empty($person->htel)) {
			    	$data = array();
			    	$htel = array();
			    	if (is_array($person->htel)) {
			    		$data = $person->htel;
			    	} else {
			    		$data[] = $person->htel;
			    	}
				    foreach ($data as $tel) {
				    	$htel[] = self::convert_tel($tel);
  					}
		    		if (count($htel) > 0) $entry['homePhone'] = $htel;
    			}
			    if (isset($person->register) && !empty($person->register)) $entry["registeredAddress"]=self::chomp_address($person->register);
	    		if (isset($person->address) && !empty($person->register)) $entry["homePostalAddress"]=self::chomp_address($person->address);
	    		if (isset($person->www) && !empty($person->register)) $entry["wWWHomePage"]=$person->www;
				
				$user_entry = $openldap->getUserEntry($entry['cn']);
				if ($user_entry) {
					$openldap->updateData($user_entry, $entry);
					$messages[] = "第 $i 筆記錄，".$person->name."學生資訊已經更新！";
				} else {
					$entry['dn'] = $user_dn;
					$openldap->createEntry($entry);
					$messages[] = "第 $i 筆記錄，".$person->name."學生資訊已經建立！";
				}
				$account_entry = $openldap->getAccountEntry($account['uid']);
				if ($account_entry) {
					$openldap->updateData($account_entry, $account);
					$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經更新！";
				} else {
					$account['dn'] = $account_dn;
					$openldap->createEntry($account);
					$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經建立！";
				}
			}
			$messages[0] = "學生資訊匯入完成！報表如下：";
			return redirect()->back()->with("success", $messages);
    	} else {
			return redirect()->back()->with("error", "檔案上傳失敗！");
    	}
	}
	
    public function schoolStudentEditForm(Request $request, $uuid = null)
	{
		$dc = $request->user()->ldap['o'];
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '教學班級');
		$my_ou = '';
		$ous = array();
		foreach ($data as $ou) {
			if (empty($my_ou)) $my_ou = $ou->ou;
			if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
		}
    	if (!is_null($uuid)) {//edit
    		$entry = $openldap->getUserEntry($uuid);
    		$user = $openldap->getUserData($entry);
			return view('admin.schoolstudentedit', [ 'my_field' => $my_field, 'keywords' => $keywords, 'ous' => $ous, 'user' => $user ]);
		} else { //add
			return view('admin.schoolstudentedit', [ 'my_field' => $my_field, 'keywords' => $keywords, 'ous' => $ous ]);
		}
	}
	
    public function createSchoolStudent(Request $request)
	{
		$dc = $request->user()->ldap['o'];
		$my_field = 'ou='.$request->get('tclass');
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$sid = $openldap->getOrgData($entry, 'tpUniformNumbers');
		$sid = $sid['tpUniformNumbers'];
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'stdno' => 'required|string',
			'seat' => 'required|integer',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$info = array();
		$info['objectClass'] = array('tpeduPerson', 'inetUser');
		$info['o'] = $dc;
		$info['employeeType'] = '學生';
		$info['inetUserStatus'] = 'active';
		$info['info'] = json_encode(array("sid" => $sid, "role" => "學生"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		$info['cn'] = $request->get('idno');
		$info['dn'] = Config::get('ldap.userattr').'='.$info['cn'].','.Config::get('ldap.userdn');
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpClass'] = $request->get('tclass');
		$info['tpSeat'] = $request->get('seat');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = $request->get('gender');
		$info['birthDate'] = $request->get('birth');
		if (!is_null($request->get('raddress'))) $info['registeredAddress'] = $request->get('raddress');
		if (!is_null($request->get('address'))) $info['homePostalAddress'] = $request->get('address');
		if (!is_null($request->get('www'))) $info['wWWHomePage'] = $request->get('www');
		if (!is_null($request->get('character'))) {
			$data = array();
			if (is_array($request->get('character'))) {
	    		$data = $request->get('character');
			} else {
	    		$data[] = $request->get('character');
			}
			$info['tpCharacter'] = $data;
		}
		if (!is_null($request->get('mail'))) {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$info['mail'] = $data;
		}
		if (!is_null($request->get('mobile'))) {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$info['mobile'] = $data;
		}
		if (!is_null($request->get('fax'))) {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$info['facsimileTelephoneNumber'] = $data;
		}
		if (!is_null($request->get('otel'))) {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$info['telephoneNumber'] = $data;
		}
		if (!is_null($request->get('htel'))) {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$info['homePhone'] = $data;
		}
				
		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect('school/teacher?field='.$my_field)->with("success", "已經為您建立學生資料！");
		} else {
			return redirect('school/teacher?field='.$my_field)->with("error", "學生新增失敗！".$openldap->error());
		}
	}
	
    public function updateSchoolStudent(Request $request, $uuid)
	{
		$dc = $request->user()->ldap['o'];
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$openldap = new LdapServiceProvider();
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'stdno' => 'required|string',
			'seat' => 'required|integer',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$info = array();
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpClass'] = $request->get('tclass');
		$info['tpSeat'] = $request->get('seat');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = (int) $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		if (is_null($request->get('raddress'))) 
			$info['registeredAddress'] = [];
		else
			$info['registeredAddress'] = $request->get('raddress');
		if (is_null($request->get('address')))
			$info['homePostalAddress'] = [];
		else
			$info['homePostalAddress'] = $request->get('address');
		if (is_null($request->get('www')))
			$info['wWWHomePage'] = [];
		else
			$info['wWWHomePage'] = $request->get('www');
		if (is_null($request->get('character'))) {
			$info['tpCharacter'] = [];
		} else {
			$data = array();
			if (is_array($request->get('character'))) {
	    		$data = $request->get('character');
			} else {
	    		$data[] = $request->get('character');
			}
			$info['tpCharacter'] = $data;
		}
		if (is_null($request->get('mail'))) {
			$info['mail'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$info['mail'] = $data;
		}
		if (is_null($request->get('mobile'))) {
			$info['mobile'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$info['mobile'] = $data;
		}
		if (is_null($request->get('fax'))) {
			$info['facsimileTelephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$info['facsimileTelephoneNumber'] = $data;
		}
		if (is_null($request->get('otel'))) {
			$info['telephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$info['telephoneNumber'] = $data;
		}
		if (!is_null($request->get('htel'))) {
			$info['homePhone'] = [];
		} else {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$info['homePhone'] = $data;
		}
				
		$entry = $openldap->getUserEntry($uuid);
		$orginal = $openldap->getUserData($entry, 'cn');
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			if ($orginal['cn'] != $request->get('idno')) {
				$result = $openldap->renameUser($original['cn'], $request->get('idno'));
				if ($result) {
					return redirect('school/teacher?field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新學生基本資料！");
				} else {
					return redirect('school/teacher?field='.$my_field.'&keywords='.$keywords)->with("error", "學生身分證字號變更失敗！".$openldap->error());
				}
			}
		} else {
			return redirect('school/teacher?field='.$my_field.'&keywords='.$keywords)->with("error", "學生基本資料變更失敗！".$openldap->error());
		}
	}
	
    public function schoolTeacherSearchForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '行政部門');
		$my_ou = $data[0]->ou;
		$my_field = $request->get('field', "ou=$my_ou");
		$keywords = $request->get('keywords');
		$request->session()->put('field', $my_field);
		$request->session()->put('keywords', $keywords);
		$ous = array();
		foreach ($data as $ou) {
			if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
		}
		if (substr($my_field,0,3) == 'ou=') {
			$my_ou = substr($my_field,3);
			if ($my_ou == 'deleted')
				$filter = "(&(o=$dc)(inetUserStatus=deleted)(employeeType=教師))";
			else
				$filter = "(&(o=$dc)(ou=$my_ou)(employeeType=教師)(!(inetUserStatus=deleted)))";
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
		$teachers = array();
		if (!empty($filter))
			$teachers = $openldap->findUsers($filter, ["cn","displayName","o","ou","title","entryUUID","inetUserStatus"]);
		if (array_key_exists('count', $teachers))
		    for ($i=0;$i<$teachers['count'];$i++) {
			    $dc = $teachers[$i]['o'][0];
			    $teachers[$i]['school']['count'] = 1;
			    $teachers[$i]['school'][0] = $openldap->getOrgTitle($dc);
			    if (array_key_exists('ou', $teachers[$i]) && $teachers[$i]['ou']['count']>0)  {
				    $ou = $teachers[$i]['ou'][0];
				    $teachers[$i]['department']['count'] = 1;
				    $teachers[$i]['department'][0] = $openldap->getOuTitle($dc, $ou);
				    if (array_key_exists('title', $teachers[$i]) && $teachers[$i]['title']['count']>0)  {
					    $role = $teachers[$i]['title'][0];
					    $teachers[$i]['titlename']['count'] = 1;
					    $teachers[$i]['titlename'][0] = $openldap->getRoleTitle($dc, $ou, $role);
				    } else {
					    $teachers[$i]['titlename']['count'] = 1;
					    $teachers[$i]['titlename'][0] = '無';
				    }
			    } else {
				    $teachers[$i]['department']['count'] = 1;
				    $teachers[$i]['department'][0] = '無';
				    $teachers[$i]['titlename']['count'] = 1;
				    $teachers[$i]['titlename'][0] = '無';
			    }
			    if (!array_key_exists('inetuserstatus', $teachers[$i]) || $teachers[$i]['inetuserstatus']['count'] == 0) {
				    $teachers[$i]['inetuserstatus']['count'] = 1;
				    $teachers[$i]['inetuserstatus'][0] = '啟用';
			    } elseif (strtolower($teachers[$i]['inetuserstatus'][0]) == 'active') {
				    $teachers[$i]['inetuserstatus'][0] = '啟用';
			    } elseif (strtolower($teachers[$i]['inetuserstatus'][0]) == 'inactive') {
				    $teachers[$i]['inetuserstatus'][0] = '停用';
			    } elseif (strtolower($teachers[$i]['inetuserstatus'][0]) == 'deleted') {
				    $teachers[$i]['inetuserstatus'][0] = '已刪除';
			    }
		    }
		return view('admin.schoolteacher', [ 'my_field' => $my_field, 'keywords' => $keywords, 'ous' => $ous, 'teachers' => $teachers ]);
    }

    public function schoolTeacherJSONForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$user = new \stdClass;
		$user->id = 'A123456789';
		$user->account = 'myaccount';
		$user->password = 'My_p@ssw0rD';
		$user->ou = 'dept02';
		$user->role = 'role014';
		$user->assign = array('606,sub01', '607,sub01', '608,sub01', '609,sub01', '610,sub01');
		$user->character = '巡迴教師 均一平台管理員';
		$user->sn = '李';
		$user->gn = '小明';
		$user->name = '李小明';
		$user->gender = 1;
		$user->birthdate = '20011105';
		$user->mail = 'johnny@tp.edu.tw';
		$user->mobile = '0900100200';
		$user->fax = '(02)23093736';
		$user->otel = '(02)23033555';
		$user->htel = '(03)3127221';
		$user->register = "臺北市中正區龍興里9鄰三元街17巷22號5樓";
		$user->address = "新北市板橋區中山路1段196號";
		$user->www = 'http://johnny.dev.io';
		$user2 = new \stdClass;
		$user2->id = 'A123456789';
		$user2->sn = '李';
		$user2->gn = '小明';
		$user2->gender = 1;
		$user2->birthdate = '20011105';
		return view('admin.schoolteacherjson', [ 'dc' => $dc, 'sample1' => $user, 'sample2' => $user2 ]);
	}
	
    public function importSchoolTeacher(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$sid = $openldap->getOrgData($entry, 'tpUniformNumbers');
		$sid = $sid['tpUniformNumbers'];
    	$messages[0] = 'heading';
    	if ($request->hasFile('json')) {
	    	$path = $request->file('json')->path();
    		$content = file_get_contents($path);
    		$json = json_decode($content);
    		if (!$json)
				return redirect()->back()->with("error", "檔案剖析失敗，請檢查 JSON 格式是否正確？");
			$rule = new idno;
			$teachers = array();
			if (is_array($json)) { //批量匯入
				$teachers = $json;
			} else {
				$teachers[] = $json;
			}
			$i = 0;
	 		foreach($teachers as $person) {
				$i++;
				if (!isset($person->name) || empty($person->name)) {
					if (empty($person->sn) || empty($person->gn)) {
						$messages[] = "第 $i 筆記錄，無教師姓名，跳過不處理！";
		    			continue;
					}
					$person->name = $person->sn.$person->gn;
				}
				if (!isset($person->id) || empty($person->id)) {
					$messages[] = "第 $i 筆記錄，無身分證字號，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'idno' => $person->id ], [ 'idno' => new idno ]
    			);
				if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."身分證字號格式或內容不正確，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'gender' => $person->gender ], [ 'gender' => 'required|digits:1' ]
    			);
    			if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."性別資訊不正確，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'date' => $person->birthdate ], [ 'date' => 'required|date' ]
				);
	    		if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."出生日期格式或內容不正確，跳過不處理！";
		    		continue;
				}
				$user_dn = Config::get('ldap.userattr')."=".$person->id.",".Config::get('ldap.userdn');
				$entry = array();
				$entry["objectClass"] = array("tpeduPerson","inetUser");
 				$entry["inetUserStatus"] = "active";
   				$entry["cn"] = $person->id;
    			$entry["sn"] = $person->sn;
    			$entry["givenName"] = $person->gn;
    			$entry["displayName"] = $person->name;
    			$entry["gender"] = $person->gender;
				$entry["birthDate"] = $person->birthdate."000000Z";
    			$entry["o"] = $dc;
    			$entry["employeeType"] = "教師";
				$entry['info'] = json_encode(array("sid" => $sid, "role" => "教師"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    			if (isset($person->ou) && !empty($person->ou)) $entry["ou"] = $person->ou;
    			if (isset($person->role) && !empty($person->role)) $entry["title"] = $person->role;
				$account = array();
   				$account["objectClass"] = "radiusObjectProfile";
			    $account["cn"] = $person->id;
			    $account["description"] = '管理員匯入';
				if (isset($person->account) && !empty($person->account))
					$account["uid"] = $person->account;
				else
					$account["uid"] = $dc.substr($person->id, -9);
    			$entry["uid"] = $account["uid"];
				if (isset($person->password) && !empty($person->password))
					$password = $openldap->make_ssha_password($person->password);
				else
					$password = $openldap->make_ssha_password(substr($person->id, -6));
	   			$account["userPassword"] = $password;
	   			$account_dn = Config::get('ldap.authattr')."=".$account['uid'].",".Config::get('ldap.authdn');
	   			$entry["userPassword"] = $password;
		    	if (isset($person->class) && !empty($person->class)) {
		    		$data = array();
		    		$classes = array();
		    		if (is_array($person->class)) {
		    			$data = $person->class;
		    		} else {
		    			$data[] = $person->class;
		    		}
		    		foreach ($data as $class) {
	    				if ($openldap->getOuEntry($dc, $class)) $classes[] = $class;
	    			}
	    			if (count($classes) > 0) $entry['tpTeachClass'] = $classes;
    			}
		    	if (isset($person->character) && !empty($person->character))
	    			$entry['tpCharacter'] = explode(' ', $person->character);
		    	if (isset($person->mail) && !empty($person->mail)) {
		    		$data = array();
		    		$mails = array();
		    		if (is_array($person->mail)) {
		    			$data = $person->mail;
		    		} else {
		    			$data[] = $person->mail;
		    		}
		    		foreach ($data as $mail) {
						$validator = Validator::make(
    						[ 'mail' => $mail ], [ 'mail' => 'email' ]
    					);
	    				if ($validator->passes()) $mails[] = $mail;
	    			}
	    			if (count($mails) > 0) $entry['mail'] = $mails;
    			}
			    if (isset($person->mobile) && !empty($person->mobile)) {
		    		$data = array();
		    		$mobiles = array();
			    	if (is_array($person->mobile)) {
			    		$data = $person->mobile;
			    	} else {
			    		$data[] = $person->mobile;
			    	}
			    	foreach ($data as $mobile) {
						$validator = Validator::make(
    						[ 'mobile' => $mobile ], [ 'mobile' => 'digits:10' ]
    					);
		    			if ($validator->passes()) $mobiles[] = $mobile;
					}
	   				if (count($mobiles) > 0) $entry['mobile'] = $mobiles;
    			}
			    if (isset($person->fax) && !empty($person->fax)) {
			    	$data = array();
			    	$fax = array();
			    	if (is_array($person->fax)) {
			    		$data = $person->fax;
			    	} else {
			    		$data[] = $person->fax;
			    	}
				    foreach ($data as $tel) {
				    	$fax[] = self::convert_tel($tel);
  					}
		    		if (count($fax) > 0) $entry['facsimileTelephoneNumber'] = $fax;
    			}
			    if (isset($person->otel) && !empty($person->otel)) {
			    	$data = array();
			    	$otel = array();
			    	if (is_array($person->otel)) {
			    		$data = $person->otel;
			    	} else {
			    		$data[] = $person->otel;
			    	}
				    foreach ($data as $tel) {
				    	$otel[] = self::convert_tel($tel);
  					}
		    		if (count($otel) > 0) $entry['telephoneNumber'] = $otel;
    			}
			    if (isset($person->htel) && !empty($person->htel)) {
			    	$data = array();
			    	$htel = array();
			    	if (is_array($person->htel)) {
			    		$data = $person->htel;
			    	} else {
			    		$data[] = $person->htel;
			    	}
				    foreach ($data as $tel) {
				    	$htel[] = self::convert_tel($tel);
  					}
		    		if (count($htel) > 0) $entry['homePhone'] = $htel;
    			}
			    if (isset($person->register) && !empty($person->register)) $entry["registeredAddress"]=self::chomp_address($person->register);
	    		if (isset($person->address) && !empty($person->register)) $entry["homePostalAddress"]=self::chomp_address($person->address);
	    		if (isset($person->www) && !empty($person->register)) $entry["wWWHomePage"]=$person->www;
			
				$user_entry = $openldap->getUserEntry($entry['cn']);
				if ($user_entry) {
					$result = $openldap->updateData($user_entry, $entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊已經更新！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊無法更新！".$openldap->error();
				} else {
					$entry['dn'] = $user_dn;
					$result = $openldap->createEntry($entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊已經建立！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊無法建立！".$openldap->error();
				}
				$account_entry = $openldap->getAccountEntry($account['uid']);
				if ($account_entry) {
					$result = $openldap->updateData($account_entry, $account);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經更新！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊無法更新！".$openldap->error();
				} else {
					$account['dn'] = $account_dn;
					$result = $openldap->createEntry($account);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經建立！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊無法建立！".$openldap->error();
				}
			}
			$messages[0] = "教師資訊匯入完成！報表如下：";
			return redirect()->back()->with("success", $messages);
    	} else {
			return redirect()->back()->with("error", "檔案上傳失敗！");
    	}
	}
	
    public function schoolTeacherEditForm(Request $request, $uuid = null)
    {
		$dc = $request->user()->ldap['o'];
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$openldap = new LdapServiceProvider();
		$data = $openldap->getSubjects($dc);
		$subjects = array();
		foreach ($data as $subj) {
			if (!array_key_exists($subj->subject, $subjects)) $subjects[$subj->subject] = $subj->description;
		}
		$data = $openldap->getOus($dc, '行政部門');
		$my_ou = '';
		$ous = array();
		foreach ($data as $ou) {
			if (empty($my_ou)) $my_ou = $ou->ou;
			if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
		}
    	if (!is_null($uuid)) {//edit
    		$entry = $openldap->getUserEntry($uuid);
    		$user = $openldap->getUserData($entry);
    		$assign = array();
    		if (array_key_exists('tpTeachClass', $user)) {
    			if (is_array($user['tpTeachClass'])) {
    				$i = 0;
    				foreach ($user['tpTeachClass'] as $pair) {
    					$part = explode(',', $pair);
    					$assign[$i]['class'] = $part[0];
    					if (isset($part[1])) $assign[$i]['subject'] = $part[1];
    					$i++;
    				}
    			} else {
    				$part = explode(',', $user['tpTeachClass']);
    				$assign[0]['class'] = $part[0];
    				if (isset($part[1])) $assign[0]['subject'] = $part[1];
    			}
    		}
    		if (array_key_exists('ou', $user))
    			$data = $openldap->getRoles($dc, $user['ou']);
    		else
    			$data = $openldap->getRoles($dc, $my_ou);
			$roles = array();
			foreach ($data as $role) {
				if (!array_key_exists($role->cn, $roles)) $roles[$role->cn] = $role->description;
			}
			return view('admin.schoolteacheredit', [ 'my_field' => $my_field, 'keywords' => $keywords, 'dc' => $dc, 'subjects' => $subjects, 'ous' => $ous, 'roles' => $roles, 'user' => $user, 'assign' => $assign ]);
		} else { //add
    		$data = $openldap->getRoles($dc, $my_ou);
			$roles = array();
			foreach ($data as $role) {
				if (!array_key_exists($role->cn, $roles)) $roles[$role->cn] = $role->description;
			}
			return view('admin.schoolteacheredit', [ 'my_field' => $my_field, 'keywords' => $keywords, 'dc' => $dc, 'subjects' => $subjects, 'ous' => $ous, 'roles' => $roles ]);
		}
	}
	
    public function createSchoolTeacher(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$my_field = 'ou='.$request->get('ou');
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$sid = $openldap->getOrgData($entry, 'tpUniformNumbers');
		$sid = $sid['tpUniformNumbers'];
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$info = array();
		$info['objectClass'] = array('tpeduPerson', 'inetUser');
		$info['o'] = $dc;
		$info['employeeType'] = '教師';
		$info['inetUserStatus'] = 'active';
		$info['info'] = json_encode(array("sid" => $sid, "role" => "教師"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		$info['cn'] = $request->get('idno');
		$info['dn'] = Config::get('ldap.userattr').'='.$info['cn'].','.Config::get('ldap.userdn');
		$info['ou'] = $request->get('ou');
		$info['title'] = $request->get('role');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = $request->get('gender');
		$info['birthDate'] = $request->get('birth');
		if (!is_null($request->get('raddress'))) $info['registeredAddress'] = $request->get('raddress');
		if (!is_null($request->get('address'))) $info['homePostalAddress'] = $request->get('address');
		if (!is_null($request->get('www'))) $info['wWWHomePage'] = $request->get('www');
		if (!is_null($request->get('tclass'))) {
			$classes = $request->get('tclass');
			$subjects = $request->get('subj');
			$assign = array();
			for ($i=0;$i<count($classes);$i++) {
	    		if ($openldap->getOuEntry($dc, $classes[$i])) {
	    			$assign[] = $classes[$i].','.$subjects[$i];
	    		}
			}
			$info['tpTeachClass'] = $assign;
		}
		if (!is_null($request->get('character'))) {
			$data = array();
			if (is_array($request->get('character'))) {
	    		$data = $request->get('character');
			} else {
	    		$data[] = $request->get('character');
			}
			$info['tpCharacter'] = $data;
		}
		if (!is_null($request->get('mail'))) {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$info['mail'] = $data;
		}
		if (!is_null($request->get('mobile'))) {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$info['mobile'] = $data;
		}
		if (!is_null($request->get('fax'))) {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$info['facsimileTelephoneNumber'] = $data;
		}
		if (!is_null($request->get('otel'))) {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$info['telephoneNumber'] = $data;
		}
		if (!is_null($request->get('htel'))) {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$info['homePhone'] = $data;
		}

		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect('school/teacher?field='.$my_field)->with("success", "已經為您建立教師資料！");
		} else {
			return redirect('school/teacher?field='.$my_field)->with("error", "教師新增失敗！".$openldap->error());
		}
	}
	
    public function updateSchoolTeacher(Request $request, $uuid)
    {
		$dc = $request->user()->ldap['o'];
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$openldap = new LdapServiceProvider();
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$info = array();
		$info['ou'] = $request->get('ou');
		$info['title'] = $request->get('role');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = (int) $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		if (is_null($request->get('raddress')))
			$info['registeredAddress'] = [];
		else
			$info['registeredAddress'] = $request->get('raddress');
		if (is_null($request->get('address')))
			$info['homePostalAddress'] = [];
		else
			$info['homePostalAddress'] = $request->get('address');
		if (is_null($request->get('www')))
			$info['wWWHomePage'] = [];
		else
			$info['wWWHomePage'] = $request->get('www');
		if (is_null($request->get('tclass'))) {
			$info['tpTeachClass'] = [];
		} else {
			$classes = $request->get('tclass');
			$subjects = $request->get('subj');
			$assign = array();
			for ($i=0;$i<count($classes);$i++) {
	    		if ($openldap->getOuEntry($dc, $classes[$i])) {
	    			$assign[] = $classes[$i].','.$subjects[$i];
	    		}
			}
			$info['tpTeachClass'] = $assign;
		}
		if (is_null($request->get('character'))) {
			$info['tpCharacter'] = [];
		} else {
			$data = array();
			if (is_array($request->get('character'))) {
	    		$data = $request->get('character');
			} else {
	    		$data[] = $request->get('character');
			}
			$info['tpCharacter'] = $data;
		}
		if (is_null($request->get('mail'))) {
			$info['mail'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$info['mail'] = $data;
		}
		if (is_null($request->get('mobile'))) {
			$info['mobile'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$info['mobile'] = $data;
		}
		if (is_null($request->get('fax'))) {
			$info['facsimileTelephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$info['facsimileTelephoneNumber'] = $data;
		}
		if (is_null($request->get('otel'))) {
			$info['telephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$info['telephoneNumber'] = $data;
		}
		if (is_null($request->get('htel'))) {
			$info['homePhone'] = [];
		} else {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$info['homePhone'] = $data;
		}
		
		$entry = $openldap->getUserEntry($uuid);
		$orginal = $openldap->getUserData($entry, 'cn');
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			if ($orginal['cn'] != $request->get('idno')) {
				$result = $openldap->renameUser($original['cn'], $request->get('idno'));
				if ($result) {
					return redirect('school/teacher?field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新教師基本資料！");
				} else {
					return redirect('school/teacher?field='.$my_field.'&keywords='.$keywords)->with("error", "教師身分證字號變更失敗！".$openldap->error());
				}
			}
		} else {
			return redirect('school/teacher?field='.$my_field.'&keywords='.$keywords)->with("error", "教師基本資料變更失敗！".$openldap->error());
		}
	}
	
    public function toggleSchoolTeacher(Request $request, $uuid)
    {
		$info = array();
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$data = $openldap->getUserData($entry, 'inetUserStatus');
		if (array_key_exists('inetUserStatus', $data) && $data['inetUserStatus'] == 'active')
			$info['inetUserStatus'] = 'inactive';
		else
			$info['inetUserStatus'] = 'active';
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經將人員標註為".($info['inetUserStatus'] == 'active' ? '啟用' : '停用')."！");
		} else {
			return redirect()->back()->with("error", "無法變更人員狀態！".$openldap->error());
		}
	}
	
    public function removeSchoolTeacher(Request $request, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$info = array();
		$info['inetUserStatus'] = 'deleted';
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經將人員標註為刪除！");
		} else {
			return redirect()->back()->with("error", "無法變更人員狀態！".$openldap->error());
		}
	}
	
    public function undoSchoolTeacher(Request $request, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$info = array();
		$info['inetUserStatus'] = 'active';
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經將人員標註為啟用！");
		} else {
			return redirect()->back()->with("error", "無法變更人員狀態！".$openldap->error());
		}
	}
	
    public function resetpass(Request $request, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$data = $openldap->getUserData($entry, array('cn', 'uid', 'mail', 'mobile'));
		if (array_key_exists('cn', $data)) {
			$idno = $data['cn'];
			$info = array();
			$info['userPassword'] = $openldap->make_ssha_password(substr($idno,-6));
		
			if (array_key_exists('cn', $data)) {
				if (is_array($data['uid'])) {
					foreach ($account as $data['uid']) {
						$account_entry = $openldap->getAccountEntry($account);
						$openldap->updateData($account_entry, $info);
					}
				} else {
					$account_entry = $openldap->getAccountEntry($data['uid']);
					$openldap->updateData($account_entry, $info);
				}
			}
			$result = $openldap->updateData($entry, $info);
			if ($result) {
				return redirect()->back()->with("success", "已經將人員密碼重設為身分證字號後六碼！");
			} else {
				return redirect()->back()->with("error", "無法變更人員密碼！".$openldap->error());
			}
		}
	}
	
    public function schoolRoleForm(Request $request, $my_ou)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '行政部門');
		if ($my_ou == 'null') $my_ou = $data[0]->ou;
		$ous = array();
		foreach ($data as $ou) {
			if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
		}
		$roles = $openldap->getRoles($dc, $my_ou);
		return view('admin.schoolrole', [ 'my_ou' => $my_ou, 'ous' => $ous, 'roles' => $roles ]);
    }

    public function createSchoolRole(Request $request, $ou)
    {
		$dc = $request->user()->ldap['o'];
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

    public function updateSchoolRole(Request $request, $ou, $role)
    {
		$dc = $request->user()->ldap['o'];
		$validatedData = $request->validate([
			'role' => 'required|string',
			'description' => 'required|string',
		]);
		$info = array();
		$info['cn'] = $request->get('role');
		$info['description'] = $request->get('description');
		
		$openldap = new LdapServiceProvider();
		if ($role != $info['cn']) {
			$users = $openldap->findUsers("(&(o=$dc)(ou=$ou)(title=$role))", "cn");
			for ($i=0;$i < $users['count'];$i++) {
	    		$idno = $users[$i]['cn'][0];
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

    public function removeSchoolRole(Request $request, $ou, $role)
    {
		$dc = $request->user()->ldap['o'];
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

    public function schoolClassAssignForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$my_grade = $request->get('grade', 1);
		$my_ou = $request->get('ou', '');
		if ($request->session()->has('grade')) $my_grade = $request->session()->get('grade');
		if ($request->session()->has('ou')) $my_ou = $request->session()->get('ou');
		$openldap = new LdapServiceProvider();
		$subjects = $openldap->getSubjects($dc);
		$grades = array();
		$classes = array();
		$data = $openldap->getOus($dc, '教學班級');
		foreach ($data as $class) {
			$grade = substr($class->ou, 0, 1);
			if (!in_array($grade, $grades)) $grades[] = $grade;
			if ($grade == $my_grade) $classes[] = $class;
		}		
		$ous = $openldap->getOus($dc, '行政部門');
		if (empty($my_ou)) $my_ou = $ous[0]->ou;
		$teachers = array();		
		$data = $openldap->findUsers("(&(o=$dc)(ou=$my_ou))", ["cn","displayName","o","ou","title"]);
		for ($i=0;$i<$data['count'];$i++) {
			$teacher = new \stdClass;
			$teacher->idno = $data[$i]['cn'][0];
			$teacher->name = $data[$i]['displayname'][0];
			$teacher->title = $openldap->getRoleTitle($dc, $data[$i]['ou'][0], $data[$i]['title'][0]);
			$teachers[] = $teacher;
		}
		return view('admin.schoolclassassign', [ 'dc' => $dc, 'my_grade' => $my_grade, 'subjects' => $subjects, 'grades' => $grades, 'classes' => $classes, 'my_ou' => $my_ou, 'ous' => $ous, 'teachers' => $teachers ]);
    }

	public function assignSchoolClass(Request $request)
	{
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$subjects = array();
		if (is_array($request->get('subjects'))) {
			$subjects = $request->get('subjects');
		} else {
			$subjects[] = $request->get('subjects');
		}
		$classes = array();
		if (is_array($request->get('classes'))) {
			$classes = $request->get('classes');
		} else {
			$classes[] = $request->get('classes');
		}
		$teachers = array();
		if (is_array($request->get('teachers'))) {
			$teachers = $request->get('teachers');
		} else {
			$teachers[] = $request->get('teachers');
		}
		$act = $request->get('act');
		$errors = array();
		foreach ($teachers as $teacher) {
			$info = array();
			$assign = array();
			foreach ($classes as $class) {
				foreach ($subjects as $subj) {
					$assign[] = "$class,$subj";
				}
			}
			$info['tpTeachClass'] = $assign;
			$entry = $openldap->getUserEntry($teacher);
			if (!$entry) continue;
			$tname = $openldap->getUserData($entry, "displayName");
			if ($act == 'add') {
				$result = $openldap->addData($entry, $info);
				if (!$result) $erros[] = $tname['displayName']."：新增配課資訊失敗！";
			} elseif ($act == 'rep') {
				$result = $openldap->updateData($entry, $info);
				if (!$result) $erros[] = $tname['displayName']."：取代配課資訊失敗！";
			} elseif ($act == 'del') {
				$result = $openldap->deleteData($entry, $info);
				if (!$result) $erros[] = $tname['displayName']."：移除配課資訊失敗！";
			}
		}
		if (count($errors) > 0) {
			return redirect()->back()->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("error", $errors);
		} else {
			if ($act == 'add') {
				return redirect()->back()->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("success", "已經為您新增配課資訊！");
			} elseif ($act == 'rep') {
				return redirect()->back()->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("success", "已經為您修改配課資訊！");
			} elseif ($act == 'del') {
				return redirect()->back()->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("success", "已經為您移除配課資訊！");
			}
		}
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
	
    public function schoolSubjectForm(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getSubjects($dc);
		return view('admin.schoolsubject', [ 'subjs' => $data ]);
    }

    public function createSchoolSubject(Request $request)
    {
		$dc = $request->user()->ldap['o'];
		$validatedData = $request->validate([
			'new-subj' => 'required|string',
			'new-desc' => 'required|string',
		]);
		$info = array();
		$info['objectClass'] = 'tpeduSubject';
		$info['tpSubject'] = $request->get('new-subj');
		$info['tpSubjectDomain'] = $request->get('new-dom');
		$info['description'] = $request->get('new-desc');
		$info['dn'] = "tpSubject=".$info['tpSubject'].",dc=$dc,".Config::get('ldap.rdn');
		$openldap = new LdapServiceProvider();
		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您建立科目！");
		} else {
			return redirect()->back()->with("error", "科目建立失敗！".$openldap->error());
		}
    }

    public function updateSchoolSubject(Request $request, $subj)
    {
		$dc = $request->user()->ldap['o'];
		$validatedData = $request->validate([
			'description' => 'required|string',
		]);
		$info = array();
		$info['tpSubjectDomain'] = $request->get('domain');
		$info['description'] = $request->get('description');
		
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getSubjectEntry($dc, $subj);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return redirect()->back()->with("success", "已經為您更新科目資訊！");
		} else {
			return redirect()->back()->with("error", "科目資訊更新失敗！".$openldap->error());
		}
    }

    public function removeSchoolSubject(Request $request, $subj)
    {
		$dc = $request->user()->ldap['o'];
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(tpTeachClass=*$subj))", "cn");
		if ($users && $users['count']>0) {
			return redirect()->back()->with("error", "此科目已經配課給老師和班級，因此無法刪除！");
		}
		$entry = $openldap->getSubjectEntry($dc, $subj);
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return redirect()->back()->with("success", "已經為您移除科目！");
		} else {
			return redirect()->back()->with("error", "科目刪除失敗！".$openldap->error());
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
	    		$idno = $users[$i]['cn'][0];
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
		if ($request->has('fax')) $info['facsimileTelephoneNumber'] = $request->get('fax');
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

	private function chomp_address($address) {
		return mb_ereg_replace("\\\\", "",$address);
	}

	private function convert_tel($tel) {
  		$ret='';
		for ($i=0; $i<strlen($tel); $i++) {
    		$charter=substr($tel,$i,1);
			$asc=ord($charter);
    		if ($asc>=48 && $asc<=57) $ret.=$charter;
  		}
  		if (substr($ret,0,3)=="886") {
    		$area = substr($ret,3,1);
    		if ($area=="8" || $area=="9") {
      			$ret="(0".substr($ret,3,3).")".substr($ret,6);
    		} else {
      			$ret = "(0".$area.")".substr($ret,4);
    		}
  		}
  		if (substr($ret,0,1)=="0") {
    		$area=substr($ret,0,2);
    		if ($area=="08" || $area=="09") {
      			$ret="(".substr($ret,0,4).")".substr($ret,4);
    		} else {
      			$ret="(".substr($ret,0,2).")".substr($ret,2);
    		}
  		} elseif (substr($ret,0,1)!="(") {
    		$ret="(02)".$ret;
  		}
  		return $ret;
  	}    
}
