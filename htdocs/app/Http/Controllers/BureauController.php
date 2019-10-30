<?php

namespace App\Http\Controllers;

use Config;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\User;
use App\Thirdapp;
use App\OauthScopeAccessLog;
use App\Usagerecord;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;

class BureauController extends Controller
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
        return view('bureau');
    }

	public function bureauThirdapp (Request $request)
	{
		$entry = $request->get('entry');

		$app = new Thirdapp;

		if(is_string($entry) && $entry != ''){
			$app = Thirdapp::where('unit','like','%'.$entry.'%')
				->orWhere('entry','like','%'.$entry.'%')
				->orWhere('background','like','%'.$entry.'%')
				//->orWhere('url','like','%'.$entry.'%')
				//->orWhere('redirect','like','%'.$entry.'%')
				->orWhere('conman','like','%'.$entry.'%')
				//->orWhere('conunit','like','%'.$entry.'%')
				->orWhere('contel','like','%'.$entry.'%')
				//->orWhere('conmail','like','%'.$entry.'%')
				->orderBy('id','asc')
				->get();
		}else{
			$entry = '';
			$app = Thirdapp::orderBy('id','asc')->get();
		}

		if($request->path() == 'thirdapp'){
			return view('thirdapp', [ 'apps' => $app, 'entry' => $entry ]);
		}else{
			return view('admin.bureauthirdapp', [ 'apps' => $app, 'entry' => $entry ]);
		}
	}

	public function updateBureauThirdappForm (Request $request, $id = null)
	{
		if(ctype_digit($id))
			$app = Thirdapp::where('id', $id)->first();
		if(!isset($app))
			return redirect('bureau/thirdapp');

		$ut = $app['unittype'];
		if($ut == '1' || $ut == '2' || $ut == '3')
			$app['unittype'.$ut] = 'Y';

		$sc = $app['scope'];

		if(is_string($sc) && $sc != ''){
			$ss = explode(" ", $sc);
			for($x = 0;$x < count($ss);$x++){
				if($ss[$x] == "me") $app['scope0'] = 'Y';
				else if($ss[$x] == "email") $app['scope1'] = 'Y';
				else if($ss[$x] == "user") $app['scope2'] = 'Y';
				else if($ss[$x] == "idno") $app['scope3'] = 'Y';
				else if($ss[$x] == "profile") $app['scope4'] = 'Y';
				else if($ss[$x] == "account") $app['scope5'] = 'Y';
				else if($ss[$x] == "school") $app['scope6'] = 'Y';
				else if($ss[$x] == "schoolAdmin") $app['scope7'] = 'Y';
			}
		}

		return view('admin.bureauthirdappedit', ['data' => $app]);
	}

	public function updateBureauThirdapp (Request $request, $id = null)
	{
		//foreach ($_REQUEST as $key => $value)
		//	file_put_contents('testlog.txt',$key.' - '.$value.PHP_EOL,FILE_APPEND);

		/*
		$v = Validator::make($request->all(), [
			'unit' => 'required',
			'recdt' => 'required',
			'conman' => 'required',
		]);

		if ($v->fails())
			return back()->withErrors($v->errors());
		*/
		$errors = [];

		if(!is_string($request->get('unit')) || $request->get('unit') == '')
			$errors['unit'] = '申請單位是必填欄位。';
		if(!is_string($request->get('entry')) || $request->get('entry') == '')
			$errors['entry'] = '應用平臺名稱是必填欄位。';

		if(!is_string($request->get('url')) || $request->get('url') == ''){
			$errors['url'] = '應用平臺網址是必填欄位。';
		}else if(strlen($request->get('url')) < 10 || (substr(strtolower($request->get('url')),0,4) != 'http' && substr(strtolower($request->get('url')),0,5) != 'https')){
			$errors['url'] = '應用平臺網址格式不正確。';
		}else{
			$where = [['url',$request->get('url')]];
			if(ctype_digit($id))
				array_push($where,['id', '<>', $id]);
			if(Thirdapp::where($where)->count() > 0)
				$errors['url'] = '應用平臺網址已存在。';
		}

		if(!is_string($request->get('redirect')) || $request->get('redirect') == ''){
			$errors['redirect'] = 'SSO認證後重導向URL是必填欄位。';
		}else if(strlen($request->get('redirect')) < 10 || (substr(strtolower($request->get('redirect')),0,4) != 'http' && substr(strtolower($request->get('redirect')),0,5) != 'https')){
			$errors['redirect'] = 'SSO認證後重導向URL格式不正確。';
		}

		if(!is_string($request->get('conman')) || $request->get('conman') == '')
			$errors['conman'] = '姓名是必填欄位。';
		if($request->get('conmail') != '' && !filter_var($request->get('conmail'), FILTER_VALIDATE_EMAIL))
			$errors['conmail'] = 'Email格式錯誤。';
		if(!is_string($request->get('contel')) || $request->get('contel') == '')
			$errors['contel'] = '電話是必填欄位。';
		if($request->get('recdt') != '' && (!ctype_digit($request->get('recdt')) || strlen($request->get('recdt')) != 8 || !checkdate(substr($request->get('recdt'),4,2),substr($request->get('recdt'),6,2),substr($request->get('recdt'),0,4))))
			$errors['recdt'] = '收件日期格式不正確。';
		if($request->get('key') == ''){
			$errors['key'] = '系統識別碼是必填欄位。';
		}else if(!ctype_digit($request->get('key')) || intval($request->get('key')) < 1){
			$errors['key'] = '系統識別碼必須是正整數。';
		}else{
			$where = [['key',$request->get('key')]];
			if(ctype_digit($id))
				array_push($where,['id', '<>', $id]);
			if(Thirdapp::where($where)->count() > 0)
				$errors['key'] = '此識別碼已被其他設定使用。';
		}

		if($request->get('stopdt') != '' && (!ctype_digit($request->get('stopdt')) || strlen($request->get('stopdt')) != 8 || !checkdate(substr($request->get('stopdt'),4,2),substr($request->get('stopdt'),6,2),substr($request->get('stopdt'),0,4))))
			$errors['stopdt'] = '撤銷日期格式不正確。';

		$unittype = null;
		$t1 = $request->get('unittype1');
		$t2 = $request->get('unittype2');
		$t3 = $request->get('unittype3');
		if(is_string($t1) && $t1 == 'Y') $unittype = '1';
		else if(is_string($t2) && $t2 == 'Y') $unittype = '2';
		else if(is_string($t3) && $t3 == 'Y') $unittype = '3';

		if($unittype == null)
			$errors['unittype'] = '單位別必須勾選。';

		$scope = "";
		$scopes = array("me","email","user","idno","profile","account","school","schoolAdmin");
		for($x = 0;$x < count($scopes);$x++){
			$s = $request->get('scope'.$x);
			if(is_string($s) && $s == 'Y') $scope .= ' '.$scopes[$x];
		}
		$scope = $scope == '' ? null : substr($scope,1);

		//if($scope == null)
		//	$errors['scope'] = '可調用資料範圍必須勾選。';

		if (sizeof($errors) > 0){
			$request->flash();
			return back()->withErrors($errors);
		}

		if($id != null){
			$app = Thirdapp::where('id', $id)->first();
			if(!isset($app))
				return back()->with("error", "找不到紀錄！");
		}else{
			$app = new Thirdapp;
		}

		$app->unit = $request->get('unit');
		$app->entry = $request->get('entry');
		$app->background = is_string($request->get('background'))?$request->get('background'):null;
		$app->url = $request->get('url');
		$app->redirect = $request->get('redirect');
		$app->unittype = $unittype;
		$app->conman = $request->get('conman');
		$app->conmail = $request->get('conmail');
		$app->conunit = is_string($request->get('conunit'))?$request->get('conunit'):null;
		$app->contel = $request->get('contel');
		$app->recdt = $request->get('recdt');
		$app->recno = is_string($request->get('recno'))?$request->get('recno'):null;
		$app->key = $request->get('key');
		$app->scope = $scope;
		$app->stopdt = $request->get('stopdt');
		$app->authyn = ($request->get('authyn') == 'Y')?'Y':'N';
		$app->stopyn = ($request->get('stopyn') == 'Y')?'Y':'N';
		$app->save();

		if($id != null)
			return redirect('bureau/thirdapp')->with("success", "資料修改完成！");

		return back()->with("success", "已新增應用平臺！");
	}

	public function removeBureauThirdapp (Request $request, $id = null)
	{
		if(ctype_digit($id))
			$app = Thirdapp::where('id', $id)->first();

		if(isset($app)){
			$name = $app->entry;
			$app->delete();
			return back()->with("success", "已刪除應用平臺『".$name."』！");
		}

		return back()->with("error", "指定的應用平臺不存在！");
	}

    public function bureauPeopleSearchForm(Request $request)
    {
		$my_field = $request->get('field');
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "st=$area";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$my_ou = '';
		if (empty($dc) && $schools) $dc = $schools[0]->o;
		if ($dc) {
			$data = $openldap->getOus($dc);
			if (!empty($data)) $my_ou = $data[0]->ou;
			if (empty($my_field) && !empty($my_ou)) $my_field = "ou=$my_ou";
		}
		$keywords = $request->get('keywords');
		$request->session()->put('area', $area);
		$request->session()->put('dc', $dc);
		$request->session()->put('field', $my_field);
		$request->session()->put('keywords', $keywords);
		$ous = array();
		if (isset($data) && $data)
			foreach ($data as $ou) {
				if (!array_key_exists($ou->ou, $ous)) $ous[$ou->ou] = $ou->description;
			}
		if (substr($my_field,0,3) == 'ou=') {
			$my_ou = substr($my_field,3);
			if ($my_ou == 'empty') {
				$filter = "(&(o=$dc)(&(!(ou=*))(!(tpClass=*))))";
			} elseif ($my_ou == 'deleted') {
				$filter = "(&(o=$dc)(inetUserStatus=deleted))";
			} else {
				$filter = "(&(o=$dc)(|(tpClass=$my_ou)(ou=*$my_ou))(!(inetUserStatus=deleted)))";
			}
		} elseif ($my_field == 'uuid' && !empty($keywords)) {
			$filter = "(&(o=$dc)(entryUUID=*".$keywords."*))";
		} elseif ($my_field == 'idno' && !empty($keywords)) {
			$filter = "(&(o=$dc)(cn=*".$keywords."*))";
		} elseif ($my_field == 'name' && !empty($keywords)) {
			$filter = "(&(o=$dc)(displayName=*".$keywords."*))";
		} elseif ($my_field == 'mail' && !empty($keywords)) {
			$filter = "(&(o=$dc)(mail=*".$keywords."*))";
		} elseif ($my_field == 'mobile' && !empty($keywords)) {
			$filter = "(&(o=$dc)(mobile=*".$keywords."*))";
		}
		$people = array();
		if (!empty($filter)) {
			$people = $openldap->findUsers($filter, [ "cn", "displayName", "uid", "employeeType", "entryUUID", "inetUserStatus" ]);
		}
		return view('admin.bureaupeople', [ 'area' => $area, 'areas' => $areas, 'dc' => $dc, 'schools' => $schools, 'ous' => $ous, 'my_field' => $my_field, 'keywords' => $keywords, 'people' => $people ]);
    }

    public function bureauPeopleEditForm(Request $request, $uuid = null)
	{
		$area = $request->session()->get('area');
		$dc = $request->session()->get('dc');
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$types = [ '教師', '學生', '校長', '職工', '主官管' ];
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		if (empty($area)) $area = $areas[0];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOrgs();
		$schools = array();
		foreach ($data as $school) {
			if (isset($school->st) && isset($school->description)) {
				$schools[$school->o]['st'] = $school->st;
				$schools[$school->o]['desc'] = $school->description;
			}
		}
		$data = $openldap->getOus($dc, '教學班級');
		$classes = array();
		if ($data)
			foreach ($data as $class) {
				if (!array_key_exists($class->ou, $classes)) $classes[$class->ou] = $class->description;
			}		
    	if (!is_null($uuid)) {//edit
    		$entry = $openldap->getUserEntry($uuid);
			$user = $openldap->getUserData($entry);
    		$org_entry = $openldap->getOrgEntry($dc);
    		$data = $openldap->getOrgData($org_entry);
    		if (array_key_exists('st', $data)) $area = $data['st'];
    		if ($user['employeeType'] != '學生') {
				return view('admin.bureauteacheredit', [ 'my_field' => $my_field, 'keywords' => $keywords, 'area' => $area, 'dc' => $dc, 'areas' => $areas, 'schools' => $schools, 'types' => $types, 'user' => $user ]);
			} else {
				return view('admin.bureaustudentedit', [ 'my_field' => $my_field, 'keywords' => $keywords, 'area' => $area, 'dc' => $dc, 'areas' => $areas, 'schools' => $schools, 'classes' => $classes, 'user' => $user ]);
			}
		} else { //add
			return view('admin.bureaupeopleedit', [ 'my_field' => $my_field, 'keywords' => $keywords, 'area' => $area, 'dc' => $dc, 'areas' => $areas, 'schools' => $schools, 'types' => $types, 'classes' => $classes ]);
		}
	}
	
    public function bureauPeopleJSONForm(Request $request)
	{
		$user = new \stdClass;
		$user->id = 'A123456789';
		$user->o = ['meps', 'bureau'];
		$user->type = '教師';
		$user->sn = '蘇';
		$user->gn = '小小';
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
		$user2->o = 'meps';
		$user2->type = '學生';
		$user2->stdno = '102247';
		$user2->class = '601';
		$user2->classtitle = '六年一班';
		$user2->seat = '7';
		$user2->sn = '蘇';
		$user2->gn = '小小';
		$user2->gender = 2;
		$user2->birthdate = '20101105';
		$user2->mail = 'johnny@tp.edu.tw';
		$user2->mobile = '0900100200';
		$user2->fax = '(02)23093736';
		$user2->otel = '(02)23033555';
		$user2->htel = '(03)3127221';
		$user2->register = "臺北市中正區龍興里9鄰三元街17巷22號5樓";
		$user2->address = "新北市板橋區中山路1段196號";
		$user->www = 'http://johnny.dev.io';
		return view('admin.bureaupeoplejson', [ 'sample1' => $user, 'sample2' => $user2 ]);
	}
	
    public function importBureauPeople(Request $request)
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
			$classes = array();
			$i = 0;
	 		foreach($teachers as $person) {
				$i++;
				if (!isset($person->name) || empty($person->name)) {
					if (empty($person->sn) || empty($person->gn)) {
						$messages[] = "第 $i 筆記錄，無真實姓名，跳過不處理！";
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
				if (!isset($person->o) || empty($person->o)) {
					$messages[] = "第 $i 筆記錄，".$person->name."無隸屬機構，跳過不處理！";
					continue;
				}
				if ($person->type == '學生') {
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
				$orgs = array();
				if (is_array($person->o)) {
					$orgs = $person->o;
				} else {
					$orgs[] = $person->o;
				}
				$educloud = array();
				foreach ($orgs as $o) {
					$sid = $openldap->getOrgId($o);
					$educloud[] = json_encode([ "sid" => $sid, "role" => $person->type ], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
				}
				$idno = strtoupper($person->id);
				$user_dn = "cn=$idno,".Config::get('ldap.userdn');
				$entry = array();
				$entry["objectClass"] = array("tpeduPerson","inetUser");
 				$entry["inetUserStatus"] = "active";
   				$entry["cn"] = $idno;
    			$entry["sn"] = $person->sn;
    			$entry["givenName"] = $person->gn;
    			if (isset($person->name)) {
					$entry["displayName"] = $person->name;
				} else {
					$entry["displayName"] = $person->sn.$person->gn;
				}
    			$entry["gender"] = (int) $person->gender;
				$entry["birthDate"] = $person->birthdate."000000Z";
    			$entry["o"] = $orgs;
				$entry['info'] = $educloud;
    			$entry["employeeType"] = $person->type;
				if ($person->type == '學生') {
    				$entry["employeeNumber"] = $person->stdno;
    				$entry["tpClass"] = $person->class;
	    			$entry["tpSeat"] = $person->seat;
					if (!in_array($person->class, $classes)) {
						$oclass = new \stdClass;
						$oclass->id = $person->class;
						$classname = $person->classtitle;
						if (empty($classname)) $classname = $person->class;
						$entry["tpClassTitle"] = $classname;
						$oclass->name = $classname;
						$classes[] = $oclass;
						unset($oclass);
					}
    			}
				$user_entry = $openldap->getUserEntry($idno);
				if (!$user_entry) {
					$account = array();
   					$account["objectClass"] = "radiusObjectProfile";
				    $account["cn"] = $idno;
				    $account["description"] = '管理員匯入';
					if ($person->type == '學生') {
						$account["uid"] = $orgs[0].$person->stdno;
					} else {
						$account["uid"] = $orgs[0].substr($idno, -9);
					}    			
					$entry["uid"] = $account["uid"];
					$password = $openldap->make_ssha_password(substr($idno, -6));
					$account["userPassword"] = $password;
					$account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
					$entry["userPassword"] = $password;
				 }
		    	if (isset($person->character)) {
		    	    if (empty($person->character))
	    			    $entry['tpCharacter'] = [];
		    	    else
	    			    $entry['tpCharacter'] = explode(' ', $person->character);
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
	    		if (isset($person->address) && !empty($person->register)) $entry["homePostalAddress"]=self::chomp_address($person->address);
	    		if (isset($person->www) && !empty($person->register)) $entry["wWWHomePage"]=$person->www;
			
				if ($user_entry) {
					$result = $openldap->updateData($user_entry, $entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."人員資訊已經更新！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."人員資訊無法更新！".$openldap->error();
				} else {
					foreach ($entry as $key => $value) {
						if (empty($value)) unset($entry[$key]);
					}
					$entry['dn'] = $user_dn;
					$result = $openldap->createEntry($entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$person->name."人員資訊已經建立！";
					else
						$messages[] = "第 $i 筆記錄，".$person->name."人員資訊無法建立！".$openldap->error();
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
			if (!empty($classes)) {
				foreach($classes as $oclass) {
					$info = array();
					$info['dn'] = "ou=$oclass->id,dc=$dc,".Config::get('ldap.rdn'); 
					$info["objectClass"] = "organizationalUnit";
					$info["ou"] = $oclass->id;
					$info["businessCategory"] = '教學班級';
					$info["description"] = $oclass->name;
					$ou_result = $openldap->getOuEntry($dc, $oclass->id);
					if (!$ou_result) $openldap->createEntry($info);
				}
			}
			$messages[0] = "人員資訊匯入完成！報表如下：";
			return back()->with("success", $messages);
    	} else {
			return back()->with("error", "檔案上傳失敗！");
    	}
	}
	
    public function createBureauPeople(Request $request)
    {
		$openldap = new LdapServiceProvider();
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$area = $request->get('area')[0];
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$idno = strtoupper($request->get('idno'));
		$orgs = $request->get('o');
		if ($openldap->checkIdno($idno))
			return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("error", "人員已經存在，所以無法新增！");
		$educloud = array();
		foreach ($orgs as $o) {
			$sid = $openldap->getOrgId($o);
			$educloud[] = json_encode([ "sid" => $sid, "role" => $request->get('type') ], JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		}
		$account = array();
		$info = array();
		$info['dn'] = "cn=$idno,".Config::get('ldap.userdn');
		$info['objectClass'] = array('tpeduPerson', 'inetUser');
		$info['cn'] = $idno;
		$info['o'] = $orgs;
		$info['info'] = $educloud;
		$info['inetUserStatus'] = 'active';
		if ($request->get('type') != '學生') {
			$info['employeeType'] = $request->get('type');
			$account["uid"] = $orgs[0].substr($idno, -9);
		} else {
			$validatedData = $request->validate([
				'stdno' => 'required|string',
				'seat' => 'required|integer',
			]);
			$info['employeeType'] = '學生';
			$info['employeeNumber'] = $request->get('stdno');
			$info['tpClass'] = $request->get('tclass');
			$info['tpSeat'] = $request->get('seat');
			$account["uid"] = $orgs[0].$info['employeeNumber'];
		}
		$account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
		$account["objectClass"] = "radiusObjectProfile";
		$account["cn"] = $idno;
		$account["description"] = '管理員新增';
		$account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
		$result = $openldap->createEntry($account);
		if (!$result) {
			return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("error", "因為預設帳號無法建立，人員新增失敗！".$openldap->error());
		}
		$info["uid"] = $account["uid"];
	    $info["userPassword"] = $account["userPassword"];
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		if (!empty($request->get('raddress'))) $info['registeredAddress'] = $request->get('raddress');
		if (!empty($request->get('address'))) $info['homePostalAddress'] = $request->get('address');
		if (!empty($request->get('www'))) $info['wWWHomePage'] = $request->get('www');
		if (!empty($request->get('character'))) {
			$data = array();
			if (is_array($request->get('character'))) {
	    		$data = $request->get('character');
			} else {
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
			return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您建立新人員！".$openldap->error());
		} else {
			return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("error", "人員新增失敗！".$openldap->error());
		}
	}

	public function bureauOauthScopeAccessLogForm(Request $request)
	{
		$a = [];
		foreach (Thirdapp::all() as $t)
			$a[$t->id] = $t->entry;

		$data = ['data' => OauthScopeAccessLog::orderBy('id','desc')->get()];

		foreach ($data['data'] as $t)
			if(array_key_exists($t->system_id, $a))
				$t->entry = $a[$t->system_id];
		$request->flash();

		return view('admin.bureauOauthScopeAccessLog', $data);
	}

	public function bureauOauthScopeAccessLog(Request $request)
	{
		$auth = $request->get('authorizer');
		$approve = $request->get('approve');
		$entry = $request->get('entry');
		$dt1 = $request->get('dt1');
		$dt2 = $request->get('dt2');
		$scope = $request->get('scope');
		$range = $request->get('scope_range');

		$a = [];

		if(is_string($auth) && $auth != '')
			array_push($a,['authorizer', 'like', '%'.$auth.'%']);
		if(is_string($approve) && $approve != '')
			array_push($a,['approve', 'like', '%'.$approve.'%']);
		if(is_string($dt1) && $dt1 != '' && is_string($dt2) && $dt2 != ''){
			$c1 = Carbon::createFromFormat('YmdHis', $dt1.'000000', 'Asia/Taipei');
			$c2 = Carbon::createFromFormat('YmdHis', $dt2.'235959', 'Asia/Taipei');
			array_push($a,['created_at', '>=', $c1]);
			array_push($a,['created_at', '<=', $c2]);
		}else if(is_string($dt1) && $dt1 != ''){
			array_push($a,['created_at', '>=', Carbon::createFromFormat('YmdHis', $dt1.'000000', 'Asia/Taipei')]);
		}else if(is_string($dt2) && $dt2 != ''){
			array_push($a,['created_at', '<=', Carbon::createFromFormat('YmdHis', $dt2.'235959', 'Asia/Taipei')]);
		}
		if(is_string($scope) && $scope != '')
			array_push($a,['scope', 'like', '%'.$scope.'%']);
		if(is_string($range) && $range != '')
			array_push($a,['scope_range', 'like', '%'.$range.'%']);

		$data = OauthScopeAccessLog::where($a);

		if(is_string($entry) && $entry != ''){
			$ary = [];
			$third = Thirdapp::where('entry', 'like', '%'.$entry.'%')->get();
			foreach($third as $t)
				array_push($ary, $t->id);
			$data = $data->whereIn('system_id', $ary);
		}

		$data = $data->orderBy('id','desc')->get();
		$request->flash();

		$b = [];
		foreach (Thirdapp::all() as $t)
			$b[$t->id] = $t->entry;

		foreach ($data as $t)
			if(array_key_exists($t->system_id, $b))
				$t->entry = $b[$t->system_id];

		return view('admin.bureauOauthScopeAccessLog', ['data' => $data]);
	}

	public function bureauUsagerecordForm(Request $request)
	{
		$request->flash();
		return view('admin.bureauusagerecord', ['data' => Usagerecord::orderBy('id','desc')->get()]);
	}

	public function bureauUsagerecord(Request $request)
	{
		$user = $request->get('user');
		$ip = $request->get('ip');
		$dt1 = $request->get('dt1');
		$dt2 = $request->get('dt2');
		$module = $request->get('module');
		$content = $request->get('content');
		$note = $request->get('note');

		$a = [];
		$c = [];
		if(is_string($user) && $user != ''){
			array_push($a,['username', 'like', '%'.$user.'%']);
			$c['username'] = $user;
		}
		if(is_string($ip) && $ip != ''){
			array_push($a,['ipaddress', 'like', '%'.$ip.'%']);
			$c['ipaddress'] = $ip;
		}
		if(is_string($dt1) && $dt1 != '' && is_string($dt2) && $dt2 != ''){
			$c1 = Carbon::createFromFormat('YmdHis', $dt1.'000000', 'Asia/Taipei');
			$c2 = Carbon::createFromFormat('YmdHis', $dt2.'235959', 'Asia/Taipei');
			array_push($a,['created_at', '>=', $c1]);
			array_push($a,['created_at', '<=', $c2]);
		}else if(is_string($dt1) && $dt1 != ''){
			array_push($a,['created_at', '>=', Carbon::createFromFormat('YmdHis', $dt1.'000000', 'Asia/Taipei')]);
		}else if(is_string($dt2) && $dt2 != ''){
			array_push($a,['created_at', '<=', Carbon::createFromFormat('YmdHis', $dt2.'235959', 'Asia/Taipei')]);
		}

		/*
		if($dt1 != '' && $dt2 != ''){
			array_push($a,['eventtime', '>=', $dt1]);
			array_push($a,['eventtime', '<=', $dt2.'99999999999999']);
			$c['dt1'] = $dt1;
			$c['dt2'] = $dt2;
		}else if($dt1 != ''){
			array_push($a,['eventtime', '>=', $dt1]);
			$c['dt1'] = $dt1;
		}else if($dt2 != ''){
			array_push($a,['eventtime', '<=', $dt2]);
			$c['dt2'] = $dt2;
		}
		*/

		if(is_string($module) && $module != ''){
			array_push($a,['module', 'like', '%'.$module.'%']);
			$c['module'] = $module;
		}
		if(is_string($content) && $content != ''){
			array_push($a,['content', 'like', '%'.$content.'%']);
			$c['content'] = $content;
		}
		if(is_string($note) && $note != ''){
			array_push($a,['note', 'like', '%'.$note.'%']);
			$c['note'] = $note;
		}

		$data = Usagerecord::where($a)->orderBy('id','desc')->get();
		$request->flash();

		return view('admin.bureauusagerecord', ['data' => $data]);//back()->withInput()->with(['data' => $data]);
	}

    public function updateBureauTeacher(Request $request, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$original = $openldap->getUserData($entry, array('cn', 'info', 'employeeType'));
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$area = $request->get('area')[0];
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$idno = strtoupper($request->get('idno'));
		$orgs = $request->get('o');
		$all_sid = array();
		foreach ($orgs as $o) {
			$sid = $openldap->getOrgId($o);
			$all_sid[] = $sid;
		}
		$info = array();
		$info['o'] = $orgs;
		$info['employeeType'] = $request->get('type');
		$educloud = array();
		if ($original['employeeType'] != $info['employeeType']) {
			if (!empty($original['info'])) {
				if (is_array($original['info'])) {
					$educloud = $original['info'];
				} else {
					$educloud[] = $original['info'];
				}
				foreach ($educloud as $k => $c) {
					$i = (array) json_decode($c, true);
					if (!in_array($i['sid'], $all_sid)) {
						unset($educloud[$k]);
					} else {
						$nk = array_search($i['sid'], $all_sid);
						unset($all_sid[$nk]);
						if ($i['role'] == $original['employeeType']) {
							unset($educloud[$k]);
							$educloud[] = json_encode(array("sid" => $i['sid'], "role" => $info['employeeType']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
						}
					}
				}
			}
		}
		foreach ($all_sid as $sid) {
			$educloud[] = json_encode(array("sid" => $sid, "role" => $info['employeeType']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		}
		$info['info'] = array_values($educloud);
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = (int) $request->get('gender');
		if (!empty($request->get('birth'))) {
			$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		}
		if (!empty($request->get('raddress'))) {
			$info['registeredAddress'] = $request->get('raddress');
		} else {
			$info['registeredAddress'] = [];
		}
		if (!empty($request->get('address'))) {
			$info['homePostalAddress'] = $request->get('address');
		} else {
			$info['homePostalAddress'] = [];
		}
		if (!empty($request->get('www'))) {
			$info['wWWHomePage'] = $request->get('www');
		} else {
			$info['wWWHomePage'] = [];
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
			$info['tpCharacter'] = array_values(array_filter($data));
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
			$info['mail'] = array_values(array_filter($data));
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
			$info['mobile'] = array_values(array_filter($data));
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
			$info['facsimileTelephoneNumber'] = array_values(array_filter($data));
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
			$info['telephoneNumber'] = array_values(array_filter($data));
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
			$info['homePhone'] = array_values(array_filter($data));
		}		
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			if ($original['cn'] != $idno) {
				$result = $openldap->renameUser($original['cn'], $idno);
				if ($result) {
	        		$model = new \App\User();
					$user = $model->newQuery()
	        		->where('idno', $original['cn'])
	        		->first();
	        		if ($user) $user->delete();
					if ($request->user()->idno == $original['cn']) Auth::logout();
					return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新教師基本資料！");
				} else {
					return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("error", "教師身分證字號變更失敗！".$openldap->error());
				}
			}
			return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新教師基本資料！");
		} else {
			return redirect('bureau/people?area='.$area.'&dc='.$orgs[0].'&field='.$my_field.'&keywords='.$keywords)->with("error", "教師基本資料變更失敗！".$openldap->error());
		}
	}
	
    public function updateBureauStudent(Request $request, $uuid)
	{
		$my_field = $request->session()->get('field');
		$keywords = $request->session()->get('keywords');
		$area = $request->get('area');
		$dc = $request->get('o');
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'stdno' => 'required|string',
			'seat' => 'required|integer',
			'gender' => 'required|integer',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$idno = strtoupper($request->get('idno'));
		$info = array();
		$info['o'] = $request->get('o');
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpClass'] = $request->get('tclass');
		$info['tpSeat'] = $request->get('seat');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = $request->get('gender');
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
	    		$data = $request->get('character');
			} else {
	    		$data[] = $request->get('character');
			}
			$info['tpCharacter'] = array_values(array_filter($data));
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
				
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$original = $openldap->getUserData($entry, 'cn');
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			if ($original['cn'] != $idno) {
				$result = $openldap->renameUser($original['cn'], $idno);
				if ($result) {
	        		$model = new \App\User();
					$user = $model->newQuery()
	        		->where('idno', $original['cn'])
	        		->first();
	        		if ($user) $user->delete();				
					return redirect('bureau/people?area='.$area.'&dc='.$dc.'&field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新學生基本資料！");
				} else {
					return redirect('bureau/people?area='.$area.'&dc='.$dc.'&field='.$my_field.'&keywords='.$keywords)->with("error", "學生身分證字號變更失敗！".$openldap->error());
				}
			}
			return redirect('bureau/people?area='.$area.'&dc='.$dc.'&field='.$my_field.'&keywords='.$keywords)->with("success", "已經為您更新學生基本資料！");
		} else {
			return redirect('bureau/people?area='.$area.'&dc='.$dc.'&field='.$my_field.'&keywords='.$keywords)->with("error", "學生基本資料變更失敗！".$openldap->error());
		}
	}
	
    public function toggleBureauPeople(Request $request, $uuid)
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
	
    public function removeBureauPeople(Request $request, $uuid)
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
	
    public function undoBureauPeople(Request $request, $uuid)
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
	
    public function resetpass(Request $request, $uuid)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$data = $openldap->getUserData($entry, array('o', 'cn', 'uid', 'mail', 'mobile', 'employeeType', 'employeeNumber'));
		$dc = $data['o'];
		$idno = $data['cn'];
		$info = array();
		$info['userPassword'] = $openldap->make_ssha_password(substr($idno,-6));

		if (array_key_exists('uid', $data) && !empty($data['uid'])) {
			//設定密碼還原後的有效期
			$fp = Config::get('app.firstPasswordChangeDay');
			if(ctype_digit(''.$fp) && $fp > 0){
				$dt = Carbon::now()->addDays($fp)->format('Ymd');
				$info['description'] = '<DEFAULT_PW_CREATEDATE>'.$dt.'</DEFAULT_PW_CREATEDATE>';
			}

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
				$user->is_change_password = 0;
				$user->save();
			}
			return back()->with("success", "已經將人員密碼重設為身分證字號後六碼！");
		} else {
			return back()->with("error", "無法變更人員密碼！".$openldap->error());
		}
	}

    public function bureauGroupForm(Request $request)
    {
		$model = [ 'mobile' => '聯絡電話', 'mail' => '郵寄清單', 'dn' => '人員目錄', 'entryUUID' => '人員代號（API使用）' ];
		$fields = [ 'employeeType' => '身份別', 'tpCharacter' => '特殊身份註記', 'inetUserStatus' => '帳號狀態' ];
		$openldap = new LdapServiceProvider();
		$data = $openldap->getGroups();
		if (!$data) $data = [];
		return view('admin.bureaugroup', [ 'model' => $model, 'fields' => $fields, 'groups' => $data ]);
    }

    public function bureauMemberForm(Request $request, $cn)
    {
		$openldap = new LdapServiceProvider();
		$data = $openldap->getMembers($cn);
		if (!$data) $data = [];
		return view('admin.bureaumember', [ 'group' => $cn, 'members' => $data ]);
    }

    public function createBureauGroup(Request $request)
    {
		$validatedData = $request->validate([
			'new-grp' => 'required|string',
		]);
		$info = array();
		$info['objectClass'] = 'groupOfURLs';
		$info['cn'] = $request->get('new-grp');
		if ($request->has('url') && !empty($request->get('url'))) {
			$info['memberURL'] = $request->get('url');
		} elseif ($request->has('perform') && !empty($request->get('perform'))) {
			$info['memberURL'] = 'ldap:///ou=people,dc=tp,dc=edu,dc=tw?'.$request->get('model').'?sub?('.$request->get('field').'='.$request->get('perform').')';
		} else {
			return back()->withInput()->with("error", "過濾條件填寫不完整！");
		}
		$info['dn'] = 'cn='.$info['cn'].','.Config::get('ldap.groupdn');
		$openldap = new LdapServiceProvider();
		$result = $openldap->createEntry($info);
		if ($result) {
			return back()->withInput()->with("success", "已經為您建立動態群組！");
		} else {
			return back()->withInput()->with("error", "動態群組建立失敗！".$openldap->error());
		}
    }

    public function updateBureauGroup(Request $request, $cn)
    {
		$info = array();
		$new_cn = $request->get('cn');
		$openldap = new LdapServiceProvider();
		$result = $openldap->renameGroup($cn, $new_cn);
		if ($result) {
			return back()->withInput()->with("success", "已經為您修改群組名稱！");
		} else {
			return back()->withInput()->with("error", "群組名稱更新失敗！".$openldap->error());
		}
    }

    public function removeBureauGroup(Request $request, $cn)
    {
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getGroupEntry($cn);
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return back()->with("success", "已經為您移除動態群組！");
		} else {
			return back()->with("error", "動態群組刪除失敗！".$openldap->error());
		}
    }

    public function bureauOrgForm(Request $request)
    {
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "st=$area";
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOrgs($filter);
		return view('admin.bureauorg', [ 'my_area' => $area, 'areas' => $areas, 'schools' => $data ]);
    }

    public function bureauOrgEditForm(Request $request, $dc = '')
    {
		$sims = [ 'alle' => '全誼', 'oneplus' => '巨耀' ];
		$category = [ '幼兒園', '國民小學', '國民中學', '高中', '高職', '大專院校', '特殊教育', '主管機關' ];
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$openldap = new LdapServiceProvider();
		if (!empty($dc)) {
			$entry = $openldap->getOrgEntry($dc);
			$data = $openldap->getOrgData($entry);
			return view('admin.bureauorgedit', [ 'data' => $data, 'areas' => $areas, 'category' => $category, 'sims' => $sims ]);
		} else {
			return view('admin.bureauorgedit', [ 'areas' => $areas, 'category' => $category, 'sims' => $sims ]);
		}
    }

    public function bureauOrgJSONForm(Request $request)
    {
		$school1 = new \stdClass;
		$school1->id = 'meps';
		$school1->sid = '353604';
		$school1->name = '台北市中正區國語實驗國民小學';
		$school1->category = '國民小學';
		$school1->area = '中正區';
		$school1->fax = '(02)23093736';
		$school1->tel = '(02)23033555';
		$school1->postal = '10001';
		$school1->address = "臺北市中正區龍興里9鄰三元街17巷22號5樓";
		$school1->mbox = '043';
		$school1->www = 'http://www.meps.tp.edu.tw';
		$school1->ipv4 = '163.21.228.0/24';
		$school1->ipv6 = '2001:288:12ce::/64';
		$school2 = new \stdClass;
		$school2->id = 'meps';
		$school2->sid = '353604';
		$school2->name = '台北市中正區國語實驗國民小學';
		$school2->category = '國民小學';
		$school2->area = '中正區';
		return view('admin.bureauorgjson', [ 'sample1' => $school1, 'sample2' => $school2 ]);
	}
	
    public function createBureauOrg(Request $request)
    {
		$openldap = new LdapServiceProvider();
		$validatedData = $request->validate([
			'dc' => 'required|string',
			'description' => 'required|string',
			'businessCategory' => 'required|string',
			'st' => 'required|string',
			'fax' => 'nullable|string',
			'telephoneNumber' => 'nullable|string',
			'postalCode' => 'nullable|digits_between:3,5',
			'street' => 'nullable|string',
			'postOfficeBox' => 'nullable|digits:3',
			'wWWHomePage' => 'nullable|url',
			'tpUniformNumbers' => 'required|string|size:6',
			'tpIpv4' => new ipv4cidr,
			'tpIpv6' => new ipv6cidr,
		]);
		$info = array();
		$info['objectClass'] = 'tpeduSchool';
		$info['o'] = $request->get('dc');
		$info['description'] = $request->get('description');
		$info['businessCategory'] = $request->get('businessCategory');
		$info['st'] = $request->get('st');
		if (!empty($request->get('fax'))) $info['facsimileTelephoneNumber'] = $request->get('fax');
		if (!empty($request->get('telephoneNumber'))) $info['telephoneNumber'] = $request->get('telephoneNumber');
		if (!empty($request->get('postalCode'))) $info['postalCode'] = $request->get('postalCode');
		if (!empty($request->get('street'))) $info['street'] = $request->get('street');
		if (!empty($request->get('postOfficeBox'))) $info['postOfficeBox'] = $request->get('postOfficeBox');
        if (!empty($request->get('wWWHomePage'))) $info['wWWHomePage'] = $request->get('wWWHomePage');
		$info['tpUniformNumbers'] = strtoupper($request->get('tpUniformNumbers'));
		$info['tpSims'] = $request->get('tpSims');
		if (!empty($request->get('tpIpv4'))) $info['tpIpv4'] = $request->get('tpIpv4');
		if (!empty($request->get('tpIpv6'))) $info['tpIpv6'] = $request->get('tpIpv6');
		$info['dn'] = "dc=".$request->get('dc').",".Config::get('ldap.rdn');
				
		if ($openldap->createEntry($info)) {
			return redirect('bureau/organization?area='.$request->get('st'))->with("success", "已經為您建立新的教育機構！");
		} else {
			return redirect('bureau/organization?area='.$request->get('st'))->with("error", "教育機構資訊新增失敗！".$openldap->error());
		}
    }

    public function updateBureauOrg(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$validatedData = $request->validate([
			'dc' => 'required|string',
			'description' => 'required|string',
			'businessCategory' => 'required|string',
			'st' => 'required|string',
			'fax' => 'nullable|string',
			'telephoneNumber' => 'nullable|string',
			'postalCode' => 'nullable|digits_between:3,5',
			'street' => 'nullable|string',
			'postOfficeBox' => 'nullable|digits:3',
			'wWWHomePage' => 'nullable|url',
			'tpUniformNumbers' => 'required|string|size:6',
			'tpIpv4' => new ipv4cidr,
			'tpIpv6' => new ipv6cidr,
		]);
		$info = array();
		$info['o'] = $request->get('dc');
		$info['description'] = $request->get('description');
		$info['businessCategory'] = $request->get('businessCategory');
		$info['st'] = $request->get('st');
		$info['facsimileTelephoneNumber'] = [];
		if (!empty($request->get('fax'))) $info['facsimileTelephoneNumber'] = $request->get('fax');
		$info['telephoneNumber'] = [];
		if (!empty($request->get('telephoneNumber'))) $info['telephoneNumber'] = $request->get('telephoneNumber');
		$info['postalCode'] = [];
		if (!empty($request->get('postalCode'))) $info['postalCode'] = $request->get('postalCode');
		$info['street'] = [];
		if (!empty($request->get('street'))) $info['street'] = $request->get('street');
		$info['postOfficeBox'] = [];
		if (!empty($request->get('postOfficeBox'))) $info['postOfficeBox'] = $request->get('postOfficeBox');
		$info['wWWHomePage'] = [];
        if (!empty($request->get('wWWHomePage'))) $info['wWWHomePage'] = $request->get('wWWHomePage');
		$info['tpSims'] = [];
		if (!empty($request->get('tpSims'))) $info['tpSims'] = $request->get('tpSims');
		$info['tpUniformNumbers'] = strtoupper($request->get('tpUniformNumbers'));
		$info['tpIpv4'] = [];
		if (!empty($request->get('tpIpv4'))) $info['tpIpv4'] = $request->get('tpIpv4');
		$info['tpIpv6'] = [];
		if (!empty($request->get('tpIpv6'))) $info['tpIpv6'] = $request->get('tpIpv6');

		$entry = $openldap->getOrgEntry($dc);
		$result1 = $openldap->updateData($entry, $info);
		if ($result1) {
			if ($dc != $request->get('dc')) {
				$result2 = $openldap->renameOrg($dc, $request->get('dc'));
				if ($result2) {
					return redirect('bureau/organization?area='.$request->get('st'))->with("success", "已經為您更新教育機構資訊！");
				} else {
					return redirect('bureau/organization?area='.$request->get('st'))->with("error", "教育機構系統代號變更失敗！".$openldap->error());
				}
			}
			return redirect('bureau/organization?area='.$request->get('st'))->with("success", "已經為您更新教育機構資訊！");
		} else {
			return redirect('bureau/organization?area='.$request->get('st'))->with("error", "教育機構資訊變更失敗！".$openldap->error());
		}
    }

    public function removeBureauOrg(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$users = $openldap->findUsers("o=$dc", "cn");
		if (!empty($users)) {
			return back()->with("error", "尚有人員隸屬於該教育機構，因此無法刪除！");
		}
		$entry = $openldap->getOrgEntry($dc);
		$ous = $openldap->getOus($dc);
		if ($ous) {
			foreach ($ous as $ou) {
				$roles = $openldap->getRoles($dc, $ou);
				foreach ($roles as $role) {
					$role_entry = $openldap->getRoleEntry($dc, $ou, $role->cn);
					$openldap->deleteEntry($role_entry);
				}
				$ou_entry = $openldap->getOuEntry($dc, $ou);
				$openldap->deleteEntry($ou_entry);
			}
		}
		$result = $openldap->deleteEntry($entry);
		if ($result) {
			return back()->with("success", "已經為您移除教育機構！");
		} else {
			return back()->with("error", "教育機構刪除失敗！".$openldap->error());
		}
    }

    public function importBureauOrg(Request $request)
    {
		$openldap = new LdapServiceProvider();
    	$messages[0] = 'heading';
    	if ($request->hasFile('json')) {
	    	$path = $request->file('json')->path();
    		$content = file_get_contents($path);
    		$json = json_decode($content);
    		if (!$json)
				return back()->with("error", "檔案剖析失敗，請檢查 JSON 格式是否正確？");
			$orgs = array();
			if (is_array($json)) { //批量匯入
				$orgs = $json;
			} else {
				$orgs[] = $json;
			}
			$i = 0;
	 		foreach($orgs as $org) {
				$i++;
				if (!isset($org->name) || empty($org->name)) {
					$messages[] = "第 $i 筆記錄，無機構全銜，跳過不處理！";
		    		continue;
				}
				if (!isset($org->id) || empty($org->id)) {
					$messages[] = "第 $i 筆記錄，無系統代號，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'sid' => $org->sid ], [ 'sid' => 'required|string|size:6' ]
    			);
				if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$org->name."統一編號格式不正確，跳過不處理！";
		    		continue;
				}
				$validator = Validator::make(
    				[ 'category' => $org->category ], [ 'category' => 'required|in:幼兒園,國民小學,國民中學,高中,高職,大專院校,特殊教育,主管機關' ]
    			);
    			if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$org->name."機構類別資訊不正確，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'area' => $org->area ], [ 'area' => 'required|in:中正區,大同區,中山區,松山區,大安區,萬華區,信義區,士林區,北投區,內湖區,南港區,文山區' ]
    			);
    			if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$org->name."行政區資訊不正確，跳過不處理！";
	    			continue;
				}
				$org_dn = "dc=$org->id,".Config::get('ldap.rdn');
				$entry = array();
				$entry["objectClass"] = array("tpeduSchool");
   				$entry["o"] = $org->id;
        		$entry['tpUniformNumbers'] = $org->sid;
		        $entry['description'] = $org->name;
		        $entry['businessCategory'] = $org->category;
		        $entry['st'] = $org->area;
			    if (isset($org->fax)) {
			    	$data = array();
			    	$fax = array();
			    	if (is_array($org->fax)) {
			    		$data = $org->fax;
			    	} else {
			    		$data[] = $org->fax;
			    	}
				    foreach ($data as $tel) {
				    	$fax[] = self::convert_tel($tel);
  					}
		    		$entry['facsimileTelephoneNumber'] = $fax;
    			}
			    if (isset($org->tel)) {
			    	$data = array();
			    	$tel = array();
			    	if (is_array($org->tel)) {
			    		$data = $org->tel;
			    	} else {
			    		$data[] = $org->tel;
			    	}
				    foreach ($data as $otel) {
				    	$tel[] = self::convert_tel($otel);
  					}
		    		$entry['telephoneNumber'] = $tel;
    			}
	    		if (isset($org->mbox) && !empty($org->mbox)) $entry["postOfficeBox"]=$org->mbox;
	    		if (isset($org->postal) && !empty($org->postal)) $entry["postalCode"]=$org->postal;
	    		if (isset($org->address) && !empty($org->address)) $entry["street"]=$org->address;
	    		if (isset($org->www) && !empty($org->www)) $entry["wWWHomePage"]=$org->www;
			    if (isset($org->ipv4)) {
			    	$net = array();
			    	if (is_array($org->ipv4)) {
			    		$data = $org->ipv4;
			    	} else {
			    		$data[] = $org->ipv4;
			    	}
				    foreach ($data as $ip) {
						$validator = Validator::make(
    						[ 'ipv4' => $ip ], [ 'ipv4' => new ipv4cidr ]
    					);
    					if ($validator->fails()) {
							$messages[] = "第 $i 筆記錄，".$org->name."IPv4 網路地址格式不正確，跳過不處理！";
	    					continue;
						}
				    	$net[] = $ip;
  					}
		    		$entry['tpIPv4'] = $net;
    			}
			    if (isset($org->ipv6)) {
			    	$net = array();
			    	if (is_array($org->ipv6)) {
			    		$data = $org->ipv6;
			    	} else {
			    		$data[] = $org->ipv6;
			    	}
				    foreach ($data as $ip) {
						$validator = Validator::make(
    						[ 'ipv6' => $ip ], [ 'ipv6' => new ipv6cidr ]
    					);
    					if ($validator->fails()) {
							$messages[] = "第 $i 筆記錄，".$org->name."IPv6 網路地址格式不正確，跳過不處理！";
	    					continue;
						}
				    	$net[] = $ip;
  					}
		    		$entry['tpIPv6'] = $net;
    			}
			
				$org_entry = $openldap->getOrgEntry($entry['o']);
				if ($org_entry) {
					$result = $openldap->updateData($org_entry, $entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$org->name."機構資訊已經更新！";
					else
						$messages[] = "第 $i 筆記錄，".$org->name."機構資訊無法更新！".$openldap->error();
				} else {
					$entry['dn'] = $org_dn;
					$result = $openldap->createEntry($entry);
					if ($result)
						$messages[] = "第 $i 筆記錄，".$org->name."機構資訊已經建立！";
					else
						$messages[] = "第 $i 筆記錄，".$org->name."機構資訊無法建立！".$openldap->error();
				}
			}
			$messages[0] = "機構資訊匯入完成！報表如下：";
			return back()->with("success", $messages);
    	} else {
			return back()->with("error", "檔案上傳失敗！");
    	}
	}

    public function bureauAdminForm(Request $request)
    {
		$admins = DB::table('users')->where('is_admin', 1)->get();
		return view('admin.bureauadmin', [ 'admins' => $admins ]);
    }

    public function addBureauAdmin(Request $request)
    {
		if ($request->has('new-admin')) {
			$openldap = new LdapServiceProvider();
	    	$validatedData = $request->validate([
				'new-admin' => new idno,
			]);
			$idno = "cn=".$request->get('new-admin');
	    	$entry = $openldap->getUserEntry($request->get('new-admin'));
			if (!$entry) {
				return back()->withInput()->with("error","您輸入的身分證字號，不存在於系統！");
	    	}
	    
			$admin = DB::table('users')->where('idno', $request->get('new-admin'))->first();	
	    	if ($admin) {
	    		DB::table('users')->where('id', $admin->id)->update(['is_admin' => 1]);
				return back()->withInput()->with("success", "已經為您新增局端管理員！");
			} else {
				return back()->withInput()->with("error", "尚未登入的人員無法設定為管理員！");
			}
	    }
    }
    
    public function delBureauAdmin(Request $request)
    {
		if ($request->has('delete-admin')) {
			$admin = DB::table('users')->where('idno', $request->get('delete-admin'))->first();	
	    	if ($admin) {
	    		DB::table('users')->where('id', $admin->id)->update(['is_admin' => 0]);
				return back()->with("success", "已經為您移除局端管理員！");
			} else {
				return back()->with("error", "找不到管理員，是否已經刪除了呢？");
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
