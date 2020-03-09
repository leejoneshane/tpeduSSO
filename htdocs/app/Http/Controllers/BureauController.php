<?php

namespace App\Http\Controllers;

use Config;
use Validator;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use App\User;
use App\Project;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;
use App\Events\ProjectAllowed;
use App\Events\ClientChange;

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

    public function listProjects(Request $request)
	{
		$projects = Project::all();
		if ($projects->isEmpty()) {
			$clients = Passport::client()->where('personal_access_client', 0)->where('password_client', 0)->get();
			if ($clients) {
				foreach ($clients as $client) {
					Project::create([
						'uuid' => (string) Str::uuid(),
						'applicationName' => $client->name,
						'redirect' => $client->redirect,
						'audit' => true,
						'client' => $client->id,
					]);	
				}
				$projects = Project::all();
			}
		}
		return view('admin.bureauproject', [ 'projects' => $projects ]);
	}

    public function createProject(Request $request)
	{
		return view('admin.bureauprojectedit');
	}

    public function storeProject(Request $request)
	{
		$validatedData = $request->validate([
            'organization' => 'required|string|max:150',
            'applicationName' => 'required|string|max:150',
            'reason' => 'required|string|max:255',
            'website' => 'required|url',
            'redirect' => 'required|url',
            'connName' => 'required|string|max:50',
            'connUnit' => 'sometimes|string|max:150',
            'connEmail' => 'required|email:rfc,dns',
            'connTel' => 'required|digits_between:7,10',
        ]);
		if ($request->get('uuid')) {
			$project = Project::where('uuid', $request->get('uuid'))->first();
			$project->forceFill([
				'organization' => $request->get('organization'),
				'applicationName' => $request->get('applicationName'),
				'reason' => $request->get('reason'),
				'website' => $request->get('website'),
				'redirect' => $request->get('redirect'),
				'privileged' => $request->get('privileged'),
				'kind' => $request->get('kind'),
				'connName' => $request->get('connName'),
				'connUnit' => $request->get('connUnit') ?: '',
				'connEmail' => $request->get('connEmail') ?: '',
				'connTel' => $request->get('connTel'),
				'memo' => $request->get('memo'),
			])->save();
			$client = $project->client();
			if ($client) {
				$client->forceFill([
					'name' => $request->get('applicationName'),
					'redirect' => $request->get('redirect'),
				])->save();	
			}
		} else {
			Project::create([
				'uuid' => (string) Str::uuid(),
				'organization' => $request->get('organization'),
				'applicationName' => $request->get('applicationName'),
				'reason' => $request->get('reason'),
				'website' => $request->get('website'),
				'redirect' => $request->get('redirect'),
				'privileged' => $request->get('privileged'),
				'kind' => $request->get('kind'),
				'connName' => $request->get('connName'),
				'connUnit' => $request->get('connUnit') ?: '',
				'connEmail' => $request->get('connEmail') ?: '',
				'connTel' => $request->get('connTel'),
				'memo' => $request->get('memo'),
			]);
		}
		return redirect()->route('bureau.project');
	}

    public function projectEditForm(Request $request, $uuid)
	{
		$project = Project::where('uuid', $uuid)->first();
		return view('admin.bureauprojectedit', [ 'project' => $project ]);	
	}

    public function removeProject(Request $request, $uuid)
	{
		$project = Project::where('uuid', $uuid)->first();
		if ($project) $project->delete();
		return redirect()->route('bureau.project');
	}

    public function showDenyProjectForm(Request $request, $uuid)
	{
		$project = Project::where('uuid', $uuid)->first();
		return view('admin.bureauprojectdeny', [ 'project' => $project ]);
	}

    public function denyProject(Request $request, $uuid)
	{
		$reason = $reguest->get('reason');
		$project = Project::where('uuid', $uuid)->first();
		if ($project) $project->reject()
			->sendMail([
				'很遺憾，您申請的介接專案已經被駁回！理由如下：',
				$reason,
				'請您補齊文件後，儘速與承辦人員聯絡，以便處理後續事宜！',
			]);
		return redirect()->route('bureau.project');
	}

    public function passProject(Request $request, $uuid)
	{
		$project = Project::where('uuid', $uuid)->first();
		if ($project) $project->allow();
		event(new ProjectAllowed($project));
		return redirect()->route('bureau.project');
	}

    public function listClients(Request $request)
	{
		$projects = Project::whereNotNull('client')->get();
		if ($projects->isEmpty()) {
			$clients = Passport::client()->where('personal_access_client', 0)->where('password_client', 0)->get();
			if ($clients) {
				foreach ($clients as $client) {
					Project::create([
						'id' => (string) Str::uuid(),
						'applicationName' => $client->name,
						'redirect' => $client->redirect,
						'audit' => true,
						'client' => $client->id,
					]);	
				}
				$projects = Project::all();
			}
		}
		return view('admin.bureauclient', [ 'projects' => $projects ]);
	}

    public function updateClient(Request $request, $uuid)
	{
		$project = Project::where('uuid', $uuid)->first();
		if ($project) $client = $project->client();
		return view('admin.bureauclientedit', [ 'project' => $project, 'client' => $client ]);
	}

    public function storeClient(Request $request, $uuid)
	{
		$project = Project::where('uuid', $uuid)->first();
		if ($project) $client = $project->client();
		$validatedData = $request->validate([
            'applicationName' => 'required|string|max:150',
            'redirect' => 'required|url',
		]);
		$project->applicationName = $request->get("applicationName");
		$project->redirect = $request->get("redirect");
		$project->save();
		$client->name = $request->get("applicationName");
		$client->redirect = $request->get("redirect");
		if ($request->get('secret')) $client->secret = Str::random(40);
		$client->save();
		event(new ClientChange($project));
		return redirect()->route('bureau.client');
	}

    public function toggleClient(Request $request, $uuid)
	{
		$project = Project::where('uuid', $uuid)->first();
		if ($project) {
			$client = $project->client();
			if ($client->revoked)
				$client->revoked = false;
			else
				$client->revoked = true;
			$client->save();
		}
		return redirect()->route('bureau.client');
	}

    public function bureauPeopleSearchForm(Request $request)
    {
		$my_field = $request->get('field');
		$areas = Config::get('app.areas');
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
		$types = Config::get('app.employeeTypes');
		$areas = Config::get('app.areas');
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
						$oclass->dc = $person->o;
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
					$info['dn'] = "ou=$oclass->id,dc=$oclass->dc,".Config::get('ldap.rdn'); 
					$info["objectClass"] = "organizationalUnit";
					$info["ou"] = $oclass->id;
					$info["businessCategory"] = '教學班級';
					$info["description"] = $oclass->name;
					$ou_result = $openldap->getOuEntry($oclass->dc, $oclass->id);
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
		$areas = Config::get('app.areas');
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "st=$area";
		$openldap = new LdapServiceProvider();
		$data = $openldap->getOrgs($filter);
		return view('admin.bureauorg', [ 'my_area' => $area, 'areas' => $areas, 'schools' => $data ]);
    }

    public function bureauOrgEditForm(Request $request, $dc = '')
    {
		$sims = Config::get('app.sims');
		$category = Config::get('app.schoolCategory');
		$areas = Config::get('app.areas');
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
    				[ 'category' => $org->category ], [ 'category' => 'required|in:'.implode(',', Config::get('app.schoolCategory')) ]
    			);
    			if ($validator->fails()) {
					$messages[] = "第 $i 筆記錄，".$org->name."機構類別資訊不正確，跳過不處理！";
	    			continue;
				}
				$validator = Validator::make(
    				[ 'area' => $org->area ], [ 'area' => 'required|in:'.implode(',', Config::get('app.areas')) ]
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
		$admins = User::where('is_admin', 1)->get();
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
	    
			$admin = User::where('idno', $request->get('new-admin'))->first();	
	    	if ($admin) {
	    		User::where('id', $admin->id)->update(['is_admin' => 1]);
				return back()->withInput()->with("success", "已經為您新增局端管理員！");
			} else {
				return back()->withInput()->with("error", "尚未登入的人員無法設定為管理員！");
			}
	    }
    }
    
    public function delBureauAdmin(Request $request)
    {
		if ($request->has('delete-admin')) {
			$admin = User::where('idno', $request->get('delete-admin'))->first();	
	    	if ($admin) {
	    		User::where('id', $admin->id)->update(['is_admin' => 0]);
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
