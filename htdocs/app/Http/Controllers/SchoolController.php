<?php

namespace App\Http\Controllers;

use Log;
use Config;
use Validator;
use Auth;
use Illuminate\Http\Request;
use App\User;
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
    public function index($dc)
    {
		$openldap = new LdapServiceProvider();
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = "";
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];

        return view('school', [ 'dc' => $dc, 'sims' => $sims ]);
    }
    
    public function schoolStudentSearchForm(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '教學班級');
		$ous = array();
		if ($data) {
			$my_ou = $data[0]->ou;
			foreach ($data as $ou) {
				if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
			}
		}
		$my_field = $request->get('field');
		if (empty($my_field) && isset($my_ou)) $my_field = "ou=$my_ou";
		$keywords = $request->get('keywords');
		$request->session()->put('field', $my_field);
		$request->session()->put('keywords', $keywords);
		if (substr($my_field,0,3) == 'ou=') {
			$my_ou = substr($my_field,3);
			if ($my_ou == 'empty') {
				$filter = "(&(o=$dc)(!(tpClass=*))(employeeType=學生))";
			} elseif ($my_ou == 'deleted') {
				$filter = "(&(o=$dc)(inetUserStatus=deleted)(employeeType=學生))";
			} else {
				$filter = "(&(o=$dc)(tpClass=$my_ou)(employeeType=學生)(!(inetUserStatus=deleted)))";
			}
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
		$students = array();
		if (!empty($filter)) {
			$students = $openldap->findUsers($filter, ["cn", "displayName", "o", "tpClass", "tpSeat", "entryUUID", "uid", "inetUserStatus"]);
			usort($students, function ($a, $b) { return $a['tpSeat'] <=> $b['tpSeat']; });
		}
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolstudent', [ 'dc' => $dc, 'sims' => $sims, 'my_field' => $my_field, 'keywords' => $keywords, 'classes' => $ous, 'students' => $students ]);
	}

    public function schoolStudentJSONForm(Request $request, $dc)
	{
		$openldap = new LdapServiceProvider();
		$user = new \stdClass;
		$user->id = 'B123456789';
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
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolstudentjson', [ 'dc' => $dc, 'sims' => $sims, 'sample1' => $user, 'sample2' => $user2 ]);
	}

    public function importSchoolStudent(Request $request, $dc)
	{
		$openldap = new LdapServiceProvider();
    	$messages[0] = "heading";
    	if ($request->hasFile('json')) {
	    	$path = $request->file('json')->path();
    		$content = file_get_contents($path);
    		$json = json_decode($content);
    		if (!$json)
				return back()->with("error", "檔案剖析失敗，請檢查 JSON 格式是否正確？");
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
				$idno = strtoupper($person->id);
				if (!isset($idno) || empty($idno)) {
					$messages[] = "第 $i 筆記錄，".$person->name."無身分證字號，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'idno' => $idno ], [ 'idno' => new idno ]
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
    			if (!isset($person->gender) || empty($person->gender)) {
					$messages[] = "第 $i 筆記錄，".$person->name."性別未輸入，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'gender' => $person->gender ], [ 'gender' => 'required|digits:1' ]
    			);
    			if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."性別資訊不正確，跳過不處理！";
	    			continue;
				}
    			if (!isset($person->birthdate) || empty($person->birthdate)) {
					$messages[] = "第 $i 筆記錄，".$person->name."出生日期未輸入，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'date' => $person->birthdate ], [ 'date' => 'required|date' ]
				);
	    		if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."出生日期格式或內容不正確，跳過不處理！";
		    		continue;
				}
				$user_dn = "cn=$idno,".Config::get('ldap.userdn');
				$user_entry = $openldap->getUserEntry($idno);
				$original = $openldap->getUserData($user_entry);
				$orgs = array();
				if ($user_entry) {
					$os = array();
					if (isset($original['o'])) {
						if (is_array($original['o'])) {
							$os = $original['o'];
						} else {
							$os[] = $original['o'];
						}
						foreach ($os as $o) {
							if ($o != $dc) $orgs[] = $o;
						}
					}
				}
				$orgs[] = $dc;
				$educloud = array();
				foreach ($orgs as $o) {
					$sid = $openldap->getOrgId($o);
					$educloud[] = json_encode(array("sid" => $sid, "role" => "學生"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
				}
				$entry = array();
				$entry["objectClass"] = array("tpeduPerson","inetUser");
 				$entry["inetUserStatus"] = "active";
   				$entry["cn"] = strtoupper($person->id);
    			$entry["sn"] = $person->sn;
    			$entry["givenName"] = $person->gn;
    			$entry["displayName"] = $person->name;
    			$entry["gender"] = (int) $person->gender;
				$entry["birthDate"] = $person->birthdate."000000Z";
    			$entry["o"] = $orgs;
				$entry['info'] = $educloud;
    			$entry["employeeType"] = "學生";
    			$entry["employeeNumber"] = $person->stdno;
    			$entry["tpClass"] = $person->class;
				$entry["tpSeat"] = $person->seat;
				if (!$user_entry) {
					$account = array();
					$account["objectClass"] = "radiusObjectProfile";
					$account["cn"] = $idno;
					$account["description"] = '管理員匯入';
					$account["uid"] = $dc.$person->stdno;
					$entry["uid"] = $account["uid"];
					$password = $openldap->make_ssha_password(substr($idno, -6));
					$account["userPassword"] = $password;
					$account['dn'] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
					$entry["userPassword"] = $password;
				 }				   
				 if (isset($person->character)) {
					if (empty($person->character)) {
						$info['tpCharacter'] = [];
					} else {
						$data = explode(' ', $person->character);
						for ($i=0;$i<count($data);$i++) {
							if ($data[$i] == '縣市管理者') unset($data[$i]);
						}
						$info['tpCharacter'] = $data;
					}
				}		  
					if (isset($person->mail)) {
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
	    			$entry['mail'] = $mails;
    			}
			    if (isset($person->mobile)) {
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
	   				$entry['mobile'] = $mobiles;
    			}
			    if (isset($person->fax)) {
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
		    		$entry['facsimileTelephoneNumber'] = $fax;
    			}
			    if (isset($person->otel)) {
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
		    		$entry['telephoneNumber'] = $otel;
    			}
			    if (isset($person->htel)) {
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
		    		$entry['homePhone'] = $htel;
    			}
			    if (isset($person->register) && !empty($person->register)) $entry["registeredAddress"]=self::chomp_address($person->register);
	    		if (isset($person->address) && !empty($person->address)) $entry["homePostalAddress"]=self::chomp_address($person->address);
	    		if (isset($person->www) && !empty($person->www)) $entry["wWWHomePage"]=$person->www;
				
				if ($user_entry) {
					$result = $openldap->updateData($user_entry, $entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."學生資訊已經更新！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."學生資訊無法更新！".$openldap->error();
				} else {
					foreach ($entry as $key => $value) {
						if (empty($value)) unset($entry[$key]);
					}
					$entry['dn'] = $user_dn;
					$result = $openldap->createEntry($entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."學生資訊已經建立！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."學生資訊無法建立！".$openldap->error();
					$account_entry = $openldap->getAccountEntry($account['uid']);
					if ($account_entry) {
						unset($account['dn']);
						$result = $openldap->updateData($account_entry, $account);
						if ($result)
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經更新！";
						else
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊無法更新！".$openldap->error();
					} else {
						$result = $openldap->createEntry($account);
						if ($result)
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經建立！";
						else
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊無法建立！".$openldap->error();
					}
				}
			}
			$messages[0] = "學生資訊匯入完成！報表如下：";
			return back()->with("success", $messages);
    	} else {
			return back()->with("error", "檔案上傳失敗！");
    	}
	}
	
    public function schoolStudentEditForm(Request $request, $dc, $uuid = null)
	{
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '教學班級');
		$ous = array();
		if ($data) {
			$my_ou = $data[0]->ou;
			foreach ($data as $ou) {
				if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
			}
		}
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
    	if (!empty($uuid)) {//edit
    		$entry = $openldap->getUserEntry($uuid);
    		$user = $openldap->getUserData($entry);
			return view('admin.schoolstudentedit', [ 'dc' => $dc, 'sims' => $sims, 'my_field' => $my_field, 'keywords' => $keywords, 'ous' => $ous, 'user' => $user ]);
		} else { //add
			return view('admin.schoolstudentedit', [ 'dc' => $dc, 'sims' => $sims, 'my_field' => $my_field, 'keywords' => $keywords, 'ous' => $ous ]);
		}
	}
	
    public function createSchoolStudent(Request $request, $dc)
	{
		$my_field = 'ou='.$request->get('tclass');
		$openldap = new LdapServiceProvider();
		$sid = $openldap->getOrgId($dc);
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
		$idno = strtoupper($request->get('idno'));
		if ($openldap->checkIdno("cn=$idno"))
			return redirect('school/'.$dc.'/teacher?field='.$my_field)->with("error", "學生已經存在，所以無法新增！");
		$account = array();
		$account["uid"] = $dc.$request->get('stdno');
		$account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
		$account["objectClass"] = "radiusObjectProfile";
		$account["cn"] = $idno;
		$account["description"] = '管理員新增';
		$account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
		$result = $openldap->createEntry($account);
		if (!$result) {
			return redirect('school/'.$dc.'/teacher?field='.$my_field)->with("error", "因為預設帳號無法建立，學生新增失敗！".$openldap->error());
		}
		$info = array();
		$info['dn'] = "cn=$idno,".Config::get('ldap.userdn');
		$info['objectClass'] = array('tpeduPerson', 'inetUser');
		$info['cn'] = $idno;
		$info["uid"] = $account["uid"];
	    $info["userPassword"] = $account["userPassword"];
		$info['o'] = $dc;
		$info['employeeType'] = '學生';
		$info['inetUserStatus'] = 'active';
		$info['info'] = json_encode(array("sid" => $sid, "role" => "學生"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpClass'] = $request->get('tclass');
		$info['tpSeat'] = $request->get('seat');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = (int) $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		if (!empty($request->get('raddress'))) $info['registeredAddress'] = $request->get('raddress');
		if (!empty($request->get('address'))) $info['homePostalAddress'] = $request->get('address');
		if (!empty($request->get('www'))) $info['wWWHomePage'] = $request->get('www');
		if (!empty($request->get('character'))) {
			$data = array();
			if (is_array($request->get('character'))) {
				foreach ($request->get('character') as $character) {
					if ($character != '縣市管理者') $data[] = $character;
			}
		} elseif ($request->get('character') != '縣市管理者') {
				$data[] = $request->get('character');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['tpCharacter'] = $data;
		}
		if (!empty($request->get('mail'))) {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['mail'] = $data;
		}
		if (!empty($request->get('mobile'))) {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['mobile'] = $data;
		}
		if (!empty($request->get('fax'))) {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['facsimileTelephoneNumber'] = $data;
		}
		if (!empty($request->get('otel'))) {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['telephoneNumber'] = $data;
		}
		if (!empty($request->get('htel'))) {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['homePhone'] = $data;
		}
				
		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect('school/'.$dc.'/student?field='.$my_field)->with("success", "已經為您建立學生資料！");
		} else {
			return redirect('school/'.$dc.'/student?field='.$my_field)->with("error", "學生新增失敗！".$openldap->error());
		}
	}
	
    public function updateSchoolStudent(Request $request, $dc, $uuid)
	{
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
		$idno = strtoupper($request->get('idno'));
		$info = array();
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpClass'] = $request->get('tclass');
		$info['tpSeat'] = $request->get('seat');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = (int) $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		if (empty($request->get('raddress'))) 
			$info['registeredAddress'] = [];
		else
			$info['registeredAddress'] = $request->get('raddress');
		if (empty($request->get('address')))
			$info['homePostalAddress'] = [];
		else
			$info['homePostalAddress'] = $request->get('address');
		if (empty($request->get('www')))
			$info['wWWHomePage'] = [];
		else
			$info['wWWHomePage'] = $request->get('www');
		if (empty($request->get('character'))) {
				$info['tpCharacter'] = [];
		} else {
				$data = array();
				if (is_array($request->get('character'))) {
					foreach ($request->get('character') as $character) {
						if ($character != '縣市管理者') $data[] = $character;
					}
				} elseif ($request->get('character') != '縣市管理者') {
					$data[] = $request->get('character');
				}
				$data = array_values(array_filter($data));
				if (!empty($data)) $info['tpCharacter'] = $data;
		}
		if (empty($request->get('mail'))) {
			$info['mail'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$info['mail'] = array_values(array_filter($data));
		}
		if (empty($request->get('mobile'))) {
			$info['mobile'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$info['mobile'] = array_values(array_filter($data));
		}
		if (empty($request->get('fax'))) {
			$info['facsimileTelephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$info['facsimileTelephoneNumber'] = array_values(array_filter($data));
		}
		if (empty($request->get('otel'))) {
			$info['telephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$info['telephoneNumber'] = array_values(array_filter($data));
		}
		if (empty($request->get('htel'))) {
			$info['homePhone'] = [];
		} else {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$info['homePhone'] = array_values(array_filter($data));
		}
				
		$entry = $openldap->getUserEntry($uuid);
		$original = $openldap->getUserData($entry, 'cn');
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			if ($original['cn'] != $idno) {
				$result = $openldap->renameUser($original['cn'], $idno);
				if ($result) {
					$user = User::where('idno', $original['cn'])->first();
	        if ($user) $user->delete();
					return redirect('school/'.$dc.'/student?field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新學生基本資料！");
				} else {
					return redirect('school/'.$dc.'/student?field='.$my_field.'&keywords='.$keywords)->with("error", "學生身分證字號變更失敗！".$openldap->error());
				}
			}
			return redirect('school/'.$dc.'/student?field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新學生基本資料！");
		} else {
			return redirect('school/'.$dc.'/student?field='.$my_field.'&keywords='.$keywords)->with("error", "學生基本資料變更失敗！".$openldap->error());
		}
	}
	
    public function schoolTeacherSearchForm(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '行政部門');
		$ous = array();
		if ($data) {
			$my_ou = $data[0]->ou;
			foreach ($data as $ou) {
				if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
			}
		}
		$my_field = $request->get('field');
		if (empty($my_field) && isset($my_ou)) $my_field = "ou=$my_ou";
		$keywords = $request->get('keywords');
		$request->session()->put('field', $my_field);
		$request->session()->put('keywords', $keywords);
		if (substr($my_field,0,3) == 'ou=') {
			$my_ou = substr($my_field,3);
			if ($my_ou == 'empty') {
				$filter = "(&(o=$dc)(!(ou=*))(!(employeeType=學生)))";
			} elseif ($my_ou == 'deleted') {
				$filter = "(&(o=$dc)(inetUserStatus=deleted)(!(employeeType=學生)))";
			} else {
				$filter = "(&(o=$dc)(ou=*$my_ou)(!(employeeType=學生))(!(inetUserStatus=deleted)))";
			}
		} elseif ($my_field == 'uuid' && !empty($keywords)) {
			$filter = "(&(o=$dc)(!(employeeType=學生))(entryUUID=*".$keywords."*))";
		} elseif ($my_field == 'idno' && !empty($keywords)) {
			$filter = "(&(o=$dc)(!(employeeType=學生))(cn=*".$keywords."*))";
		} elseif ($my_field == 'name' && !empty($keywords)) {
			$filter = "(&(o=$dc)(!(employeeType=學生))(displayName=*".$keywords."*))";
		} elseif ($my_field == 'mail' && !empty($keywords)) {
			$filter = "(&(o=$dc)(!(employeeType=學生))(mail=*".$keywords."*))";
		} elseif ($my_field == 'mobile' && !empty($keywords)) {
			$filter = "(&(o=$dc)(!(employeeType=學生))(mobile=*".$keywords."*))";
		}
		$teachers = array();
		if (!empty($filter)) {
			$teachers = $openldap->findUsers($filter, ["cn","displayName","o","ou","title","entryUUID","uid","inetUserStatus"]);
		}
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolteacher', [ 'dc' => $dc, 'sims' => $sims, 'my_field' => $my_field, 'keywords' => $keywords, 'ous' => $ous, 'teachers' => $teachers ]);
    }

    public function schoolTeacherJSONForm(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$user = new \stdClass;
		$user->id = 'A123456789';
		$user->ou = array('dept02', 'dept07');
		$user->role = 'dept02,role014';
		$user->tclass = array('606,sub01', '607,sub01', '608,sub01', '609,sub01', '610,sub01');
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
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolteacherjson', [ 'dc' => $dc, 'sims' => $sims, 'sample1' => $user, 'sample2' => $user2 ]);
	}
	
    public function importSchoolTeacher(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
    	$messages[0] = 'heading';
    	if ($request->hasFile('json')) {
	    	$path = $request->file('json')->path();
    		$content = file_get_contents($path);
    		$json = json_decode($content);
    		if (!$json)
				return back()->with("error", "檔案剖析失敗，請檢查 JSON 格式是否正確？");
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
				$idno = strtoupper($person->id);
				if (!isset($idno) || empty($idno)) {
					$messages[] = "第 $i 筆記錄，".$person->name."無身分證字號，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'idno' => $idno ], [ 'idno' => new idno ]
    			);
				if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."身分證字號格式或內容不正確，跳過不處理！";
		    		continue;
				}
    			if (!isset($person->gender) || empty($person->gender)) {
					$messages[] = "第 $i 筆記錄，".$person->name."性別未輸入，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'gender' => $person->gender ], [ 'gender' => 'required|digits:1' ]
    			);
    			if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."性別資訊不正確，跳過不處理！";
	    			continue;
				}
    			if (!isset($person->birthdate) || empty($person->birthdate)) {
					$messages[] = "第 $i 筆記錄，".$person->name."出生日期未輸入，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'birth' => $person->birthdate ], [ 'birth' => 'required|date' ]
				);
	    		if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$person->name."出生日期格式或內容不正確，跳過不處理！";
		    		continue;
				}
				$user_dn = "cn=$idno,".Config::get('ldap.userdn');
				$user_entry = $openldap->getUserEntry($idno);
				$original = $openldap->getUserData($user_entry);
				$orgs = array();
				$units = array();
				$roles = array();
				$assign = array();
				if ($user_entry) {
					$os = array();
					if (isset($original['o'])) {
						if (is_array($original['o'])) {
							$os = $original['o'];
						} else {
							$os[] = $original['o'];
						}
						foreach ($os as $o) {
							if ($o != $dc) $orgs[] = $o;
						}
					}
					$ous = array();
					if (isset($original['ou'])) {
						if (is_array($original['ou'])) {
							$ous = $original['ou'];
						} else {
							$ous[] = $original['ou'];
						}
						foreach ($ous as $ou_pair) {
							$a = explode(',', $ou_pair);
							if (count($a) == 2 && $a[0] != $dc) $units[] = $ou_pair;
						}
					}
					$titles = array();
					if (isset($original['title'])) {
						if (is_array($original['title'])) {
							$titles = $original['title'];
						} else {
							$titles[] = $original['title'];
						}
						foreach ($titles as $title_pair) {
							$a = explode(',', $title_pair);
							if (count($a) == 3 && $a[0] != $dc) $roles[] = $title_pair;
						}
					}
					$tclass = array();
					if (isset($original['tpTeachClass'])) {
						if (is_array($original['tpTeachClass'])) {
							$tclass = $original['tpTeachClass'];
						} else {
							$tclass[] = $original['tpTeachClass'];
						}
						foreach ($tclass as $pair) {
							$a = explode(',', $pair);
							if (count($a) == 3 && $a[0] != $dc) $assign[] = $pair;
						}
					}
				} else {
					$account = array();
					$account["objectClass"] = "radiusObjectProfile";
					$account["cn"] = $idno;
					$account["description"] = '管理員匯入';
					$account["uid"] = $dc.substr($idno, -9);
					$info["uid"] = $account["uid"];
					$password = $openldap->make_ssha_password(substr($idno, -6));
					$account["userPassword"] = $password;
					$account['dn'] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
					$info["userPassword"] = $password;
				}
				$orgs[] = $dc;
				$educloud = array();
				foreach ($orgs as $o) {
					$sid = $openldap->getOrgId($o);
					$educloud[] = json_encode(array("sid" => $sid, "role" => "教師"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
				}
				$ous = array();
				if (isset($person->ou)) {
					if (is_array($person->ou)) {
						$ous = $person->ou;
					} else {
						$ous[] = $person->ou;
					}
					foreach ($ous as $ou) {
						if (!in_array("$dc,$ou", $units)) $units[] = "$dc,$ou";
					}
				}
				$titles = array();
				if (isset($person->role)) {
					if (is_array($person->role)) {
						$titles = $person->role;
					} else {
						$titles[] = $person->role;
					}
					foreach ($titles as $title_pair) {
						if (!in_array("$dc,$title_pair", $roles)) $roles[] = "$dc,$title_pair";
					}
				}			
				$classes = array();
				if (isset($person->tclass)) {
					if (is_array($person->tclass)) {
						$classes = $person->tclass;
					} else {
						$classes[] = $person->tclass;
					}
		    		foreach ($classes as $class_pair) {
						$a = explode(',', $class_pair);
						if ($openldap->getOuEntry($dc, $a[0])) $assign[] = "$dc,$class_pair";
	    			}
				}
				$info = array();
				$info["objectClass"] = array("tpeduPerson","inetUser");
 				$info["inetUserStatus"] = "active";
   				$info["cn"] = $idno;
    			$info["sn"] = $person->sn;
    			$info["givenName"] = $person->gn;
    			$info["displayName"] = $person->name;
				$info['gender'] = (int) $person->gender;
				$info['birthDate'] = $person->birthdate.'000000Z';
    			$info["o"] = $orgs;
    			$info["ou"] = $units;
    			$info["title"] = $roles;
    			$info["employeeType"] = "教師";
				$info['info'] = $educloud;
    			$info['tpTeachClass'] = $assign;
				if (isset($person->character)) {
		    	    if (empty($person->character)) {
	    			    $info['tpCharacter'] = [];
							} else {
								$data = explode(' ', $person->character);
								for ($i=0;$i<count($data);$i++) {
									if ($data[$i] == '縣市管理者') unset($data[$i]);
								}
								$info['tpCharacter'] = $data;
							}
	    		}
		    	if (isset($person->mail)) {
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
	    			$info['mail'] = $mails;
    			}
			    if (isset($person->mobile)) {
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
	   				$info['mobile'] = $mobiles;
    			}
			    if (isset($person->fax)) {
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
		    		$info['facsimileTelephoneNumber'] = $fax;
    			}
			    if (isset($person->otel)) {
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
		    		$info['telephoneNumber'] = $otel;
    			}
			    if (isset($person->htel)) {
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
		    		$info['homePhone'] = $htel;
    			}
			    if (isset($person->register) && !empty($person->register)) $info["registeredAddress"]=self::chomp_address($person->register);
	    		if (isset($person->address) && !empty($person->address)) $info["homePostalAddress"]=self::chomp_address($person->address);
	    		if (isset($person->www) && !empty($person->www)) $info["wWWHomePage"]=$person->www;
			
				if ($user_entry) {
					$result = $openldap->updateData($user_entry, $info);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊已經更新！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊無法更新！".$openldap->error();
				} else {
					foreach ($info as $key => $value) {
						if (empty($value)) unset($info[$key]);
					}
					$info['dn'] = $user_dn;
					$result = $openldap->createEntry($info);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊已經建立！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."教師資訊無法建立！".$openldap->error();
					$account_entry = $openldap->getAccountEntry($account['uid']);
					if ($account_entry) {
						unset($account['dn']);
						$result = $openldap->updateData($account_entry, $account);
						if ($result)
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經更新！";
						else
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊無法更新！".$openldap->error();
					} else {
						$result = $openldap->createEntry($account);
						if ($result)
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊已經建立！";
						else
							$messages[] = "第 $i 筆記錄，".$person->name."帳號資訊無法建立！".$openldap->error();
					}
				}
			}
			$messages[0] = "教師資訊匯入完成！報表如下：";
			return back()->with("success", $messages);
    	} else {
			return back()->with("error", "檔案上傳失敗！");
    	}
	}
	
    public function schoolTeacherEditForm(Request $request, $dc, $uuid = null)
    {
		$types = [ '教師', '校長', '職工' ];
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$openldap = new LdapServiceProvider();
		$data = $openldap->getSubjects($dc);
		$subjects = array();
		if ($data) {
			foreach ($data as $subj) {
				if (!array_key_exists($subj['tpSubject'], $subjects)) {
					$subj_id = $subj['tpSubject'];
					$subjects[$subj_id] = $subj['description'];
				}
			}
		}
		$data = $openldap->getOus($dc, '教學班級');
		$classes = array();
		if ($data) {
			foreach ($data as $class) {
				if (!array_key_exists($class->ou, $classes)) $classes[$class->ou] = $class->description;
			}
		}
		$data = $openldap->allRoles($dc);
		$roles = array();
		if ($data) {
			foreach ($data as $role) {
				if (!array_key_exists($role->cn, $roles)) $roles[$role->cn] = $role->description;
			}
		}
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		if (!empty($uuid)) {//edit
    		$entry = $openldap->getUserEntry($uuid);
    		$user = $openldap->getUserData($entry);
    		$assign = array();
    		if (array_key_exists('tpTeachClass', $user)) {
				if (is_array($user['tpTeachClass'])) {
					$info = $user['tpTeachClass'];
				} else {
					$info[] = $user['tpTeachClass'];
				}
    			$i = 0;
    			foreach ($info as $pair) {
					$a = explode(',', $pair);
					if (count($a)==3 && $a[0] == $dc) {
						$assign[$i]['class'] = $a[1];
						$assign[$i]['subject'] = $a[2];
						$i++;
					} else {
						$assign[$i]['class'] = $a[0];
						$assign[$i]['subject'] = '';
						if (isset($a[1])) $assign[$i]['subject'] = $a[1];
						$i++;
					}
    			}
			}
			return view('admin.schoolteacheredit', [ 'dc' => $dc, 'sims' => $sims, 'my_field' => $my_field, 'keywords' => $keywords, 'types' => $types, 'subjects' => $subjects, 'classes' => $classes, 'roles' => $roles, 'assign' => $assign, 'user' => $user ]);
		} else { //add
			return view('admin.schoolteacheredit', [ 'dc' => $dc, 'sims' => $sims, 'my_field' => $my_field, 'keywords' => $keywords, 'types' => $types, 'subjects' => $subjects, 'classes' => $classes, 'roles' => $roles ]);
		}
	}
	
    public function createSchoolTeacher(Request $request, $dc)
    {
		$my_field = 'ou='.$request->get('ou');
		$openldap = new LdapServiceProvider();
		$sid = $openldap->getOrgId($dc);
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'gender' => 'required|digits:1',
			'birth' => 'required|date',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$idno = strtoupper($request->get('idno'));
		if ($openldap->checkIdno("cn=$idno"))
			return redirect('school/'.$dc.'/teacher?field='.$my_field)->with("error", "教師已經存在，所以無法新增！");
		$account = array();
		$account["uid"] = $dc.substr($idno, -9);
		$account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
		$account["objectClass"] = "radiusObjectProfile";
		$account["cn"] = $idno;
		$account["description"] = '管理員新增';
		$account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
		$result = $openldap->createEntry($account);
		if (!$result) {
			return redirect('school/'.$dc.'/teacher?field='.$my_field)->with("error", "因為預設帳號無法建立，教師新增失敗！".$openldap->error());
		}
		$info = array();
		$info['objectClass'] = array('tpeduPerson', 'inetUser');
		$info['o'] = $dc;
		$info['employeeType'] = $request->get('type');
		$info['inetUserStatus'] = 'active';
		$info['info'] = json_encode(array("sid" => $sid, "role" => $info['employeeType']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		$info['cn'] = $idno;
		$info['dn'] = "cn=$idno,".Config::get('ldap.userdn');
		$units = array();
		$roles = array();
		$titles = $request->get('roles');
		if (!empty($titles)) {
			foreach ($titles as $title_pair) {
				$a = explode(',', $title_pair);
				if (!in_array("$dc,$a[0]", $units)) $units[] = "$dc,$a[0]";
				$roles[] = "$dc,$title_pair";
			}
		}
		$info['ou'] = $units;
		$info['title'] = $roles;
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info["uid"] = $account["uid"];
	    $info["userPassword"] = $account["userPassword"];
		$info['gender'] = (int) $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		if (!empty($request->get('tclass'))) {
			$classes = $request->get('tclass');
			$subjects = $request->get('subj');
			$assign = array();
			for ($i=0;$i<count($classes);$i++) {
	    		if ($openldap->getOuEntry($dc, $classes[$i])) {
	    			$assign[] = $dc.",".$classes[$i].','.$subjects[$i];
	    		}
			}
			$info['tpTeachClass'] = $assign;
		}
		if (!empty($request->get('raddress'))) $info['registeredAddress'] = $request->get('raddress');
		if (!empty($request->get('address'))) $info['homePostalAddress'] = $request->get('address');
		if (!empty($request->get('www'))) $info['wWWHomePage'] = $request->get('www');
		if (!empty($request->get('character'))) {
			$data = array();
			if (is_array($request->get('character'))) {
				foreach ($request->get('character') as $character) {
					if ($character != '縣市管理者') $data[] = $character;
			}
		} elseif ($request->get('character') != '縣市管理者') {
				$data[] = $request->get('character');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['tpCharacter'] = $data;
		}
		if (!empty($request->get('mail'))) {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['mail'] = $data;
		}
		if (!empty($request->get('mobile'))) {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['mobile'] = $data;
		}
		if (!empty($request->get('fax'))) {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['facsimileTelephoneNumber'] = $data;
		}
		if (!empty($request->get('otel'))) {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['telephoneNumber'] = $data;
		}
		if (!empty($request->get('htel'))) {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['homePhone'] = $data;
		}

		$result = $openldap->createEntry($info);
		if ($result) {
			return redirect('school/'.$dc.'/teacher?field='.$my_field)->with("success", "已經為您建立教師資料！");
		} else {
			return redirect('school/'.$dc.'/teacher?field='.$my_field)->with("error", "教師新增失敗！".$openldap->error());
		}
	}
	
    public function updateSchoolTeacher(Request $request, $dc, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$sid = $openldap->getOrgId($dc);
		$entry = $openldap->getUserEntry($uuid);
		$original = $openldap->getUserData($entry, [ 'cn', 'o', 'ou', 'employeeType', 'title', 'tpTeachClass', 'info' ]);
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'gender' => 'required|digits:1',
			'birth' => 'required|date',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$idno = strtoupper($request->get('idno'));
		$info = array();
		$info['employeeType'] = $request->get('type');
		if ($original['employeeType'] != $info['employeeType']) {
			$educloud = array();
			if (!empty($original['info'])) {
				if (is_array($original['info'])) {
					$educloud = $original['info'];
				} else {
					$educloud[] = $original['info'];
				}
				foreach ($educloud as $k => $c) {
					$i = (array) json_decode($c, true);
					if (array_key_exists('sid', $i) && $i['sid'] == $sid) unset($educloud[$k]);
				}
			}
			$educloud[] = json_encode(array("sid" => $sid, "role" => $info['employeeType']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
			$info['info'] = array_values($educloud);
		}
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = (int) $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		$ous = array();
		$units = array();
		if (isset($original['ou'])) {
			if (is_array($original['ou'])) {
				$ous = $original['ou'];
			} else {
				$ous[] = $original['ou'];
			}
			foreach ($ous as $ou_pair) {
				$a = explode(',', $ou_pair);
				if (count($a) == 2 && $a[0] != $dc) $units[] = $ou_pair;
			}
		}
		$titles = array();
		$roles = array();
		if (isset($original['title'])) {
			if (is_array($original['title'])) {
				$titles = $original['title'];
			} else {
				$titles[] = $original['title'];
			}
			foreach ($titles as $title_pair) {
				$a = explode(',', $title_pair);
				if (count($a) == 3 && $a[0] != $dc) $roles[] = $title_pair;
			}
		}
		$titles = $request->get('roles');
		if (!empty($titles)) {
			foreach ($titles as $title_pair) {
				$a = explode(',', $title_pair);
				if (!in_array("$dc,$a[0]", $units)) $units[] = "$dc,$a[0]";
				$roles[] = "$dc,$title_pair";
			}
		}
		$info['ou'] = $units;
		$info['title'] = $roles;
		if (empty($request->get('raddress')))
			$info['registeredAddress'] = [];
		else
			$info['registeredAddress'] = $request->get('raddress');
		if (empty($request->get('address')))
			$info['homePostalAddress'] = [];
		else
			$info['homePostalAddress'] = $request->get('address');
		if (empty($request->get('www')))
			$info['wWWHomePage'] = [];
		else
			$info['wWWHomePage'] = $request->get('www');
		
		$assign = array();
		if (isset($original['tpTeachClass'])) {
			if (is_array($original['tpTeachClass'])) {
				$tclass = $original['tpTeachClass'];
			} else {
				$tclass[] = $original['tpTeachClass'];
			}
			foreach ($tclass as $pair) {
				$a = explode(',', $pair);
				if (count($a) == 3 && $a[0] != $dc) $assign[] = $pair;
			}
		}
		if (!empty($request->get('tclass'))) {
			$classes = $request->get('tclass');
			$subjects = $request->get('subj');
			for ($i=0;$i<count($classes);$i++) {
	    		if ($openldap->getOuEntry($dc, $classes[$i])) {
	    			$assign[] = $dc.",".$classes[$i].','.$subjects[$i];
	    		}
			}
		}
		$info['tpTeachClass'] = $assign;
		if (empty($request->get('character'))) {
			$info['tpCharacter'] = [];
		} else {
			$data = array();
			if (is_array($request->get('character'))) {
				foreach ($request->get('character') as $character) {
					if ($character != '縣市管理者') $data[] = $character;
				}
			} elseif ($request->get('character') != '縣市管理者') {
				$data[] = $request->get('character');
			}
			$data = array_values(array_filter($data));
			if (!empty($data)) $info['tpCharacter'] = $data;
		}
		if (empty($request->get('mail'))) {
			$info['mail'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$info['mail'] = array_values(array_filter($data));
		}
		if (empty($request->get('mobile'))) {
			$info['mobile'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$info['mobile'] = array_values(array_filter($data));
		}
		if (empty($request->get('fax'))) {
			$info['facsimileTelephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('fax'))) {
	    		$data = $request->get('fax');
			} else {
	    		$data[] = $request->get('fax');
			}
			$info['facsimileTelephoneNumber'] = array_values(array_filter($data));
		}
		if (empty($request->get('otel'))) {
			$info['telephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$info['telephoneNumber'] = array_values(array_filter($data));
		}
		if (empty($request->get('htel'))) {
			$info['homePhone'] = [];
		} else {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$info['homePhone'] = array_values(array_filter($data));
		}
		
		$entry = $openldap->getUserEntry($uuid);
		$original = $openldap->getUserData($entry, 'cn');
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			if ($original['cn'] != $idno) {
				$result = $openldap->renameUser($original['cn'], $idno);
				if ($result) {
					$user = User::where('idno', $original['cn'])->first();
	        		if ($user) $user->delete();
					if ($request->user()->idno == $original['cn']) Auth::logout();
					return redirect('school/'.$dc.'/teacher?field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新教師基本資料！");
				} else {
					return redirect('school/'.$dc.'/teacher?field='.$my_field.'&keywords='.$keywords)->with("error", "教師身分證字號變更失敗！".$openldap->error());
				}
			}
			return redirect('school/'.$dc.'/teacher?field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新教師基本資料！");
		} else {
			return redirect('school/'.$dc.'/teacher?field='.$my_field.'&keywords='.$keywords)->with("error", "教師基本資料變更失敗！".$openldap->error());
		}
	}
	
    public function toggle(Request $request, $dc, $uuid)
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
			return back()->with("success", "已經將人員標註為".($info['inetUserStatus'] == 'active' ? '啟用' : '停用')."！");
		} else {
			return back()->with("error", "無法變更人員狀態！".$openldap->error());
		}
	}
	
    public function remove(Request $request, $dc, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$info = array();
		$info['inetUserStatus'] = 'deleted';
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return back()->with("success", "已經將人員標註為刪除！");
		} else {
			return back()->with("error", "無法變更人員狀態！".$openldap->error());
		}
	}
	
    public function undo(Request $request, $dc, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$info = array();
		$info['inetUserStatus'] = 'active';
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return back()->with("success", "已經將人員標註為啟用！");
		} else {
			return back()->with("error", "無法變更人員狀態！".$openldap->error());
		}
	}
	
    public function resetpass(Request $request, $dc, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$data = $openldap->getUserData($entry, array('cn', 'uid', 'mail', 'mobile', 'employeeType', 'employeeNumber'));
		$idno = $data['cn'];
		$info = array();
		$info['userPassword'] = $openldap->make_ssha_password(substr($idno,-6));
		
		if (array_key_exists('uid', $data) && !empty($data['uid'])) {
			if (is_array($data['uid'])) {
				foreach ($data['uid'] as $account) {
					$account_entry = $openldap->getAccountEntry($account);
					$openldap->updateData($account_entry, $info);
				}
			} else {
				$account_entry = $openldap->getAccountEntry($data['uid']);
				$openldap->updateData($account_entry, $info);
			}
		} else {
			$account = array();
			if ($data['employeeType'] != '學生') {
				$account["uid"] = $dc.substr($idno, -9);
			} else {
				$account["uid"] = $dc.$data['employeeNumber'];
			}
			$account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
			$account["objectClass"] = "radiusObjectProfile";
			$account["cn"] = $idno;
			$account["description"] = '管理員新增';
			$account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
			$openldap->createEntry($account);
			$info["uid"] = $account["uid"];
		}
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			$user = User::where('idno', $idno)->first();
			if ($user) {
				$user->password = \Hash::make(substr($idno,-6));
				$user->save();
			}
			return back()->with("success", "已經將人員密碼重設為身分證字號後六碼！");
		} else {
			return back()->with("error", "無法變更人員密碼！".$openldap->error());
		}
	}
	
    public function schoolRoleForm(Request $request, $dc, $my_ou)
    {
		$ous = array();
		$roles = array();
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '行政部門');
		if ($data) {
			if (empty($my_ou)) $my_ou = $data[0]->ou;
			foreach ($data as $ou) {
				if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
			}
			if ($my_ou) $roles = $openldap->getRoles($dc, $my_ou);
		}
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolrole', [ 'dc' => $dc, 'sims' => $sims, 'my_ou' => $my_ou, 'ous' => $ous, 'roles' => $roles ]);
    }

    public function createSchoolRole(Request $request, $dc, $ou)
    {
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
			return back()->withInput()->with("success", "已經為您建立職務！");
		} else {
			return back()->withInput()->with("error", "職務建立失敗！".$openldap->error());
		}
    }

    public function updateSchoolRole(Request $request, $dc, $ou, $role)
    {
		$validatedData = $request->validate([
			'role' => 'required|string',
			'description' => 'required|string',
		]);
		$info = array();
		$info['cn'] = $request->get('role');
		$info['description'] = $request->get('description');
		
		$openldap = new LdapServiceProvider();
		if ($role != $info['cn']) {
			$users = $openldap->findUsers("(&(o=$dc)(ou=*$ou)(title=*$role))", [ "cn", "o", "ou", "title" ]);
			foreach ($users as $user) {
	    		$idno = $user['cn'];
				$user_entry = $openldap->getUserEntry($idno);
				$roles = array();
				$titles = array();
				if (isset($user['title'])) {
					if (is_array($user['title'])) {
						$titles = $user['title'];
					} else {
						$titles[] = $user['title'];
					}
				}
				foreach ($titles as $title) {
					$a = explode(',', $title);
					if (count($a) == 3 && ($a[0] != $dc || $a[1] != $ou || $a[2] != $role)) $roles[] = $title; 
					if (count($a) == 1 && $a[0] != $role) $roles[] = $dc.','.$ou.','.$title;
				}
				$roles = array_values(array_unique($roles + [ $dc.','.$ou.','.$info['cn'] ]));
	    		$openldap->updateData($user_entry, [ 'title' => $roles ]);
			}
		}
		$entry = $openldap->getRoleEntry($dc, $ou, $role);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return back()->withInput()->with("success", "已經為您更新職務資訊！");
		} else {
			return back()->withInput()->with("error", "職務資訊更新失敗！".$openldap->error());
		}
    }

    public function removeSchoolRole(Request $request, $dc, $ou, $role)
    {
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(ou=*$ou)(title=*$role))", "cn");
		if (!empty($users)) {
			return back()->with("error", "尚有人員從事該職務，因此無法刪除！");
		}
		$entry = $openldap->getRoleEntry($dc, $ou, $role);
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return back()->with("success", "已經為您移除職務！");
		} else {
			return back()->with("error", "職務刪除失敗！".$openldap->error());
		}
    }

    public function schoolClassForm(Request $request, $dc)
    {
		$my_grade = $request->get('grade', 1);
		$my_ou = $request->get('ou', '');
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOus($dc, '教學班級');
		if (empty($data)) {
			$users = $openldap->findUsers("&(o=$dc)(employeeType=學生)");
			$grades = array();
			$classes = array();
			$all_classes = array();
			if ($users)
				foreach ($users as $user) {
					$idno = $user['cn'];
					$cid = $user['tpClass'];
					if (!empty($cid)) {
						$class = new \stdClass();
						$class->ou = $cid;
						$class->grade = substr($cid, 0, 1);
						$class->description = substr($cid,0,1).'年'.intval(substr($cid,-2)).'班';
						if ($cid && !in_array($cid, $all_classes)) {
							$all_classes[] = $class;
							if (!in_array($class->grade, $grades)) $grades[] = $class->grade;
							if ($class->grade == $my_grade) $classes[] = $class;
						}
					}
				}
			if (!empty($all_classes))
				foreach($all_classes as $class) {
					$class_entry = $openldap->getOuEntry($dc, $class->ou);
					if (!$class_entry) {
						$info = array();
						$info["objectClass"] = array("organizationalUnit", "top");
						$info["ou"] = $class->ou;
						$info["businessCategory"] = '教學班級';
						$info["description"] = $class->description;
						$info["dn"] = "ou=$class->ou,dc=$dc,".Config::get('ldap.rdn');
						$openldap->createEntry($info);
					}
				}
		} else {
			$grades = array();
			$classes = array();
			foreach ($data as $class) {
				if (!in_array($class->grade, $grades)) $grades[] = $class->grade;
				if ($class->grade == $my_grade) $classes[] = $class;
			}
		}
		$teachers = array();
		$ous = $openldap->getOus($dc, '行政部門');
		if (!empty($ous)) {
			if (empty($my_ou)) $my_ou = $ous[0]->ou;
			foreach ($ous as $ou) {
				if (strpos($ou->description, '級任') || strpos($ou->description, '導師')) {
					$my_ou = $ou->ou;
				}
			}
			$teachers = $openldap->findUsers("(&(o=$dc)(ou=*$my_ou))");
		}
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolclass', [ 'dc' => $dc, 'sims' => $sims, 'my_grade' => $my_grade, 'grades' => $grades, 'classes' => $classes, 'my_ou' => $my_ou, 'ous' => $ous, 'teachers' => $teachers ]);
    }

    public function schoolClassAssignForm(Request $request, $dc)
    {
		$my_grade = $request->get('grade', 1);
		$my_ou = $request->get('ou', '');
		if ($request->session()->has('grade')) $my_grade = $request->session()->get('grade');
		if ($request->session()->has('ou')) $my_ou = $request->session()->get('ou');
		$openldap = new LdapServiceProvider();
		$subjects = $openldap->getSubjects($dc);
		$grades = array();
		$classes = array();
		$data = $openldap->getOus($dc, '教學班級');
		if ($data)
			foreach ($data as $class) {
				$grade = substr($class->ou, 0, 1);
				if (!in_array($grade, $grades)) $grades[] = $grade;
				if ($grade == $my_grade) $classes[] = $class;
			}
		$ous = $openldap->getOus($dc, '行政部門');
		if (empty($my_ou) && !empty($ous)) $my_ou = $ous[0]->ou;
		$teachers = $openldap->findUsers("(&(o=$dc)(ou=*$my_ou))");
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolclassassign', [ 'dc' => $dc, 'sims' => $sims, 'my_grade' => $my_grade, 'subjects' => $subjects, 'grades' => $grades, 'classes' => $classes, 'my_ou' => $my_ou, 'ous' => $ous, 'teachers' => $teachers ]);
    }

	public function assignSchoolClass(Request $request, $dc)
	{
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
					$assign[] = "$dc,$class,$subj";
				}
			}
			$entry = $openldap->getUserEntry($teacher);
			if (!$entry) continue;
			$data = $openldap->getUserData($entry, [ "displayName", "o", "tpTeachClass" ]);
			$tname = $data['displayName'];
			$info['tpTeachClass'] = $assign;
			if ($act == 'add') {
				$result = $openldap->addData($entry, $info);
				if (!$result) $erros[] = $tname."：新增配課資訊失敗！";
			} elseif ($act == 'rep') {
				$tclass = $data['tpTeachClass'];
				if (!is_array($tclass)) $tclass[] = $tclass;
				foreach ($tclass as $assign_pair) {
					$a = explode(',', $assign_pair);
					if (count($a) == 3 && $a[0] != $dc) {
						$assign[] = $assign_pair;
					}
				}
				$result = $openldap->updateData($entry, [ "tpTeachClass" => $assign ]);
				if (!$result) $erros[] = $tname."：取代配課資訊失敗！";
			} elseif ($act == 'del') {
				$result = $openldap->deleteData($entry, $info);
				if (!$result) $erros[] = $tname."：移除配課資訊失敗！";
			}
		}
		if (count($errors) > 0) {
			return back()->withInput()->with('dc', $dc)->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("error", $errors);
		} else {
			if ($act == 'add') {
				return back()->withInput()->with('dc', $dc)->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("success", "已經為您新增配課資訊！");
			} elseif ($act == 'rep') {
				return back()->withInput()->with('dc', $dc)->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("success", "已經為您修改配課資訊！");
			} elseif ($act == 'del') {
				return back()->with('dc', $dc)->with('grade', $request->get('grade'))->with('ou', $request->get('ou'))->with("success", "已經為您移除配課資訊！");
			}
		}
	}

    public function createSchoolClass(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$validatedData = $request->validate([
			'new-ou' => 'required|digits:3',
			'new-desc' => 'required|string',
		]);
		$class = $request->get('new-ou');
		$idno = $request->get('new-teacher');
		if (!empty($idno)) {
			$teacher = $openldap->getUserEntry($idno);
			if ($teacher) $openldap->updateData($teacher, [ 'tpTutorClass' => $class ]);
		}
		$info = array();
		$info['objectClass'] = array('organizationalUnit', 'top');
		$info['businessCategory']='教學班級'; //右列選一:行政部門,教學領域,教師社群或社團,學生社團或營隊
		$info['ou'] = $class;
		$info['description'] = $request->get('new-desc');
		$info['dn'] = "ou=$class,dc=$dc,".Config::get('ldap.rdn');
		$result = $openldap->createEntry($info);
		if ($result) {
			return back()->withInput()->with("success", "已經為您建立班級！");
		} else {
			return back()->withInput()->with("error", "班級建立失敗！".$openldap->error());
		}
	}
	
    public function updateSchoolClass(Request $request, $dc, $class)
    {
		$validatedData = $request->validate([
			'description' => 'required|string',
		]);
		$info = array();
		$info['description'] = $request->get('description');

		$openldap = new LdapServiceProvider();
		$idno = $request->get($class.'teacher');
		if (!empty($idno)) {
			$teacher = $openldap->getUserEntry($idno);
			if ($teacher) $openldap->updateData($teacher, [ 'tpTutorClass' => $class ]);
		}
		$users = $openldap->findUsers("(&(o=$dc)(tpClass=$class))", "cn");
		foreach ($users as $user) {
	    	$idno = $user['cn'];
	    	$user_entry = $openldap->getUserEntry($idno);
	    	$openldap->updateData($user_entry, ['tpClassTitle' => $info['description'] ]);
		}
		$entry = $openldap->getOUEntry($dc, $class);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return back()->withInput()->with("success", "已經為您更新班級資訊！");
		} else {
			return back()->withInput()->with("error", "班級資訊更新失敗！".$openldap->error());
		}
	}
	
    public function removeSchoolClass(Request $request, $dc, $class)
    {
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(tpClass=$class))", "cn");
		if (!empty($users)) {
			return back()->with("error", "尚有人員隸屬於該班級，因此無法刪除！");
		}
		$teacher = $openldap->findUsers("(&(o=$dc)(tpTutorClass=$class))", 'cn');
		if ($teacher) {
			$idno = $teacher[0]['cn'];
			$teacher_entry = $openldap->getUserEntry($idno);
			if ($teacher_entry) $openldap->deleteData($teacher_entry, [ 'tpTutorClass' => [] ]);
		}
		$entry = $openldap->getOUEntry($dc, $class);
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return back()->with("success", "已經為您移除班級！");
		} else {
			return back()->with("error", "班級刪除失敗！".$openldap->error());
		}
	}
	
    public function schoolSubjectForm(Request $request, $dc)
    {
		$domains = [ '生活', '語文', '數學', '社會', '自然科學', '藝術', '綜合活動', '科技', '健康與體育', '彈性課程', '教育議題' ];
		$openldap = new LdapServiceProvider();
		$subjs= $openldap->getSubjects($dc);
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolsubject', [ 'dc' => $dc, 'sims' => $sims, 'domains' => $domains, 'subjs' => $subjs ]);
    }

    public function createSchoolSubject(Request $request, $dc)
    {
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
			return back()->withInput()->with("success", "已經為您建立科目！");
		} else {
			return back()->withInput()->with("error", "科目建立失敗！".$openldap->error());
		}
    }

    public function updateSchoolSubject(Request $request, $dc, $subj)
    {
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
			return back()->withInput()->with("success", "已經為您更新科目資訊！");
		} else {
			return back()->withInput()->with("error", "科目資訊更新失敗！".$openldap->error());
		}
    }

    public function removeSchoolSubject(Request $request, $dc, $subj)
    {
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(tpTeachClass=*,$subj))", "cn");
		if (!empty($users)) {
			return back()->with("error", "此科目已經配課給老師和班級，因此無法刪除！");
		}
		$entry = $openldap->getSubjectEntry($dc, $subj);
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return back()->with("success", "已經為您移除科目！");
		} else {
			return back()->with("error", "科目刪除失敗！".$openldap->error());
		}
    }

    public function schoolUnitForm(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$ous = $openldap->getOus($dc, '行政部門');
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolunit', [ 'dc' => $dc, 'sims' => $sims, 'ous' => $ous ]);
    }

    public function createSchoolUnit(Request $request, $dc)
    {
		$validatedData = $request->validate([
			'new-ou' => 'required|string',
			'new-desc' => 'required|string',
		]);
		$info = array();
		$info['objectClass'] = array('organizationalUnit', 'top');
		$info['businessCategory']='行政部門'; //右列選一:行政部門,教學領域,教師社群或社團,學生社團或營隊
		$info['ou'] = $request->get('new-ou');
		$info['description'] = $request->get('new-desc');
		$info['dn'] = "ou=".$info['ou'].",dc=$dc,".Config::get('ldap.rdn');
		$openldap = new LdapServiceProvider();
		$result = $openldap->createEntry($info);
		if ($result) {
			return back()->withInput()->with("success", "已經為您建立行政部門！");
		} else {
			return back()->withInput()->with("error", "行政部門建立失敗！".$openldap->error());
		}
    }

    public function updateSchoolUnit(Request $request, $dc, $ou)
    {
		$validatedData = $request->validate([
			'ou' => 'required|string',
			'description' => 'required|string',
		]);
		$info = array();
		$info['ou'] = $request->get('ou');
		$info['description'] = $request->get('description');
		
		$openldap = new LdapServiceProvider();
		if ($ou != $info['ou']) {
			$users = $openldap->findUsers("(&(o=$dc)(ou=*$ou))", [ "cn", "o", "ou" ]);
			foreach ($users as $user) {
	    		$idno = $user['cn'];
				$user_entry = $openldap->getUserEntry($idno);
				$units = array();
				$ous = array();
				if (isset($user['ou'])) {
					if (is_array($user['ou'])) {
						$ous = $user['ou'];
					} else {
						$ous[] = $user['ou'];
					}
				}
				foreach ($ous as $ou_pair) {
					$a = explode(',', $ou_pair);
					if (count($a) == 2 && ($a[0] != $dc || $a[1] != $ou)) $units[] = $ou_pair; 
					if (count($a) == 1 && $a[0] != $ou) $units[] = $dc.','.$ou_pair;
				}
				$units = array_values(array_unique($units + [ $dc.','.$info['cn'] ]));
	    		$openldap->updateData($user_entry, [ 'ou' => $units ]);
			}
		}

		$entry = $openldap->getOUEntry($dc, $ou);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return back()->withInput()->with("success", "已經為您更新行政部門資訊！");
		} else {
			return back()->withInput()->with("error", "行政部門資訊更新失敗！".$openldap->error());
		}
    }

    public function removeSchoolUnit(Request $request, $dc, $ou)
    {
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("(&(o=$dc)(ou=*$ou))", "cn");
		if (!empty($users)) {
			return back()->with("error", "尚有人員隸屬於該行政部門，因此無法刪除！");
		}
		$entry = $openldap->getOUEntry($dc, $ou);
		$roles = $openldap->getRoles($dc, $ou);
		foreach ($roles as $role) {
			$role_entry = $openldap->getRoleEntry($dc, $ou, $role->cn);
			$openldap->deleteEntry($role_entry);
		}
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return back()->with("success", "已經為您移除行政部門！");
		} else {
			return back()->with("error", "行政部門刪除失敗！".$openldap->error());
		}
    }

    public function schoolProfileForm(Request $request, $dc)
    {
		$categorys = [ '幼兒園', '國民小學', '國民中學', '高中', '高職', '大專院校', '特殊教育', '主管機關' ];
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$openldap = new LdapServiceProvider();
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		return view('admin.schoolprofile', [ 'categorys' => $categorys, 'dc' => $dc, 'sims' => $sims, 'data' => $data, 'areas' => $areas ]);
    }

    public function updateSchoolProfile(Request $request, $dc)
    {
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
			'tpUniformNumbers' => 'required|string|size:6',
			'tpIpv4' => new ipv4cidr,
			'tpIpv6' => new ipv6cidr,
		]);
		$info = array();
		$info['description'] = $request->get('description');
		$info['businessCategory'] = $request->get('businessCategory');
		$info['st'] = $request->get('st');
		$info['facsimileTelephoneNumber'] = [];
		if (!empty($request->get('fax'))) $info['facsimileTelephoneNumber'] = $request->get('fax');
		$info['telephoneNumber'] = $request->get('telephoneNumber');
		$info['postalCode'] = $request->get('postalCode');
		$info['street'] = $request->get('street');
		$info['postOfficeBox'] = $request->get('postOfficeBox');
		$info['wWWHomePage'] = [];
        if (!empty($request->get('wWWHomePage'))) $info['wWWHomePage'] = $request->get('wWWHomePage');
		$info['tpUniformNumbers'] = strtoupper($request->get('tpUniformNumbers'));
		$info['tpIpv4'] = [];
		if (!empty($request->get('tpIpv4'))) $info['tpIpv4'] = $request->get('tpIpv4');
		$info['tpIpv6'] = [];
		if (!empty($request->get('tpIpv6'))) $info['tpIpv6'] = $request->get('tpIpv6');

		$entry = $openldap->getOrgEntry($dc);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return back()->withInput()->with("success", "已經為您更新學校基本資料！");
		} else {
			return back()->withInput()->with("error", "學校基本資料變更失敗！".$openldap->error());
		}
    }

    public function schoolAdminForm(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, "tpSims");
		$sims = '';
		if (array_key_exists('tpSims', $data)) $sims = $data['tpSims'];
		$admins = array();
		if (array_key_exists('tpAdministrator', $data)) {
		    if (is_array($data['tpAdministrator'])) {
				$ids = $data['tpAdministrator'];
			} else {
				$ids[] = $data['tpAdministrator'];
			}
			foreach ($ids as $idno) {
				$admin = new \stdClass;
				$admin->idno = $idno;
				$admin->name = $openldap->getUserName($idno);
				$admins[] = $admin;
			}
		}
		return view('admin.schooladminwithsidebar', [ 'admins' => $admins, 'dc' => $dc, 'sims' => $sims ]);
    }

    public function showSchoolAdminSettingForm(Request $request)
    {
		if ($request->session()->has('dc')) {
		    $dc = $request->session()->pull('dc');
		} else {
		    return redirect('/');
		}
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($entry, "tpAdministrator");
		$admins = array();
		if (array_key_exists('tpAdministrator', $data)) {
		    if (is_array($data['tpAdministrator'])) {
				$ids = $data['tpAdministrator'];
			} else {
				$ids[] = $data['tpAdministrator'];
			}
			foreach ($ids as $idno) {
				$admin = new \stdClass;
				$admin->idno = $idno;
				$admin->name = $openldap->getUserName($idno);
				$admins[] = $admin;
			}
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
		    $idno = $request->get('new-admin');
	    	$entry = $openldap->getUserEntry($idno);
		    if ($entry) {
				$orgs = array();
				$data = $openldap->getUserData($entry, [ "o", "cn", "tpAdminSchools" ]);
				if (array_key_exists('tpAdminSchools', $data)) {
					if (is_array($data['tpAdminSchools'])) {
						$orgs = $data['tpAdminSchools'];
					} elseif (!empty($data['tpAdminSchools'])) {
						$orgs[] = $data['tpAdminSchools'];
					}
				}
				$orgs[] = $dc;
				$orgs = array_values(array_unique($orgs));
				$openldap->updateData($entry, [ 'tpAdminSchools' => $orgs ]);
		    } else {
				return back()->withInput()->with("error","您輸入的身分證字號，不存在於系統！");
	    	}
	    
		    $entry = $openldap->getOrgEntry($dc);
		    $result1 = $openldap->addData($entry, [ 'tpAdministrator' => $idno ]);
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
	    	$result2 = $openldap->updateData($entry, [ 'userPassword' => $ssha ]);
		    if ($result2) {
				$messages .= "密碼已經變更完成！";
	    	} else {
				$messages .= "密碼無法寫入資料庫，請稍後再試一次！";
		    }
		}
		if ($result1 && $result2) {
			return back()->with("success", $messages);
		} else {
			return back()->withInput()->with("error", $messages.$openldap->error());
		}
    }
    
	public function delSchoolAdmin(Request $request)
	{
		$dc = $request->get('dc');
		$openldap = new LdapServiceProvider();
		if ($request->has('delete-admin')) {
			$idno = $request->get('delete-admin');
			$entry = $openldap->getUserEntry($idno);
			if ($entry) {
				$orgs = array();
				$data = $openldap->getUserData($entry, [ "o", "cn", "tpAdminSchools" ]);
				if (array_key_exists('tpAdminSchools', $data)) {
					if (is_array($data['tpAdminSchools'])) {
						$orgs = $data['tpAdminSchools'];
					} elseif (!empty($data['tpAdminSchools'])) {
						$orgs[] = $data['tpAdminSchools'];
					}
				}
				$orgs = array_values(array_diff($orgs, [$dc]));
				$openldap->updateData($entry, [ 'tpAdminSchools' => $orgs ]);
			}
			$org_entry = $openldap->getOrgEntry($dc);
			$result = $openldap->deleteData($org_entry, [ 'tpAdministrator' => $idno ]);
			if ($result) {
				return back()->with("success","已經為您刪除學校管理員！");
			} else {
				return back()->with("error","管理員刪除失敗，請稍後再試一次！".$openldap->error());
			}
		}
	}

	private function chomp_address($address)
	{
		return mb_ereg_replace("\\\\", "",$address);
	}

	private function convert_tel($tel)
	{
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
