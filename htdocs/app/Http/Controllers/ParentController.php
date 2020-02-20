<?php

namespace App\Http\Controllers;

use Auth;
use Log;
use Carbon\Carbon;
use App\User;
use App\PSLink;
use App\OauthScopeField;
use App\OauthThirdappStudent;
use Illuminate\Http\Request;
use App\Providers\SimsServiceProvider;
use App\Providers\LdapServiceProvider;
use App\Providers\GoogleServiceProvider;

class ParentController extends Controller
{

	public function index()
	{
		$idno = Auth::user()->idno;
		$kids = PSLink::where('parent_idno', $idno)->where('verified', 1)->orderBy('created_at','desc')->first();
		return view('parents.home', [ 'kids' => $kids ]);
	}

	public function listLink(Request $request)
	{
		$openldap = new LdapServiceProvider();
		$idno = Auth::user()->idno;
		$links = PSLink::where('parent_idno', $idno)->orderBy('created_at','desc')->get();
		$kids = array();
		foreach ($links as $l) {
			$link_id = $l->id;
			$student_idno = $l->student_idno;
			$entry = $openldap->getUserEntry($student_idno);
			$data = $openldap->getUserData($entry);
			$school = $openldap->getOrgTitle($data['o']);
			$k = array();
			$k['idno'] = $idno;
			$k['stdno'] = $data['employeeNumber'];
			$k['name'] = $data['displayName'];
			$k['school'] = $school;
			$k['class'] = $data['tpClass'];
			$k['seat'] = $data['tpSeat'];
			$kids[$link_id] = $k;
		}
		return view('parents.listLink', [ 'links' => $links, 'kids' => $kids ]);
	}

	public function showLinkForm(Request $request)
    {
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "st=$area";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		if (empty($dc) && $schools) $dc = $schools[0]->o;
		return view('parents.linkEdit', [ 'areas' => $areas, 'area' => $area, 'schools' => $schools, 'dc' => $dc ]);
	}
	
	public function applyLink(Request $request)
    {
		$validatedData = $request->validate([
			'dc' => 'required|string',
			'stdno' => 'required|string',
			'relation' => 'required|string',
		]);
		$sims = new SimsServiceProvider();
		$openldap = new LdapServiceProvider();
		$user = Auth::user();
		$dc = $request->get('dc');
		$stdno = $request->get('stdno');
		$relation = $request->get('relation');
		$students = $openldap->findUsers("(&(o=$dc)(employeeType=學生)(employeeNumber=$stdno))", 'cn');
		$idno = $students[0]['cn'];
		$info = array();
		$info['parent_idno'] = $user->idno;
		$info['student_idno'] = $idno;
		$info['relation'] = $relation;
		$org = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($org);
		$sims = $data['tpSims'];
		if ($sims == 'alle') {
			$uno = $data['tpUniformNumbers'];
			$parents = $sims->ps_call('student_parents_info', [ 'sid' => $uno, 'stdno' => $stdno ]);
			$match = false;
			foreach ($parents as $p) {
				if ($p->name == $user->name && $p->telephone == $user->mobile && $p->relation == $relation) {
					$match = true;
					break;
				}
			}
			if ($match) {
				$info['verified'] = 1;
				$info['verified_time'] = time();
			}
		}
		PSLink::create($info);
		return redirect()->route('parent.listLink');
	}
	
	public function removeLink(Request $request, $id)
    {
		PSLink::find($id)->delete();
		return back()->with("success","已經為您移除親子連結！");
	}

	public function showAuthProxyForm(Request $request)
    {
		$openldap = new LdapServiceProvider();
		//取家長的學生
		$yearsAgo12=date("Ymd",strtotime("-12 year"));
		$data = StudentParentRelation::where('parent_idno',$userNow->idno)->where('status','1')->orderBy('created_at','desc')->get();
		
		$dataList = [];
		$studentData = [];
		$agreeAll=[];
		$agreeList=[];	
		$student_id=0;	
		if($request->session()->has('student')) $student_id=$request->session()->pull('student');
		if(!empty($request->get('student'))) $student_id=$request->get('student');
		if(!empty($data)) {
			foreach ($data as $d) {
				$cc=[];
				$entry = $openldap->getUserEntry($d['student_idno']);
				if ($entry) {
					//判斷是否為12歲以下
					$studentRow = $openldap->getUserData($entry);
					$cc['student_name']=$studentRow['displayName'];
					//改抓LDAP學生生日進行判斷是否有大於12歲 
					if(substr($studentRow['birthDate'],0,8)>=$yearsAgo12) {						
						$cc['wantAgree']='1';
					} else {
						$cc['wantAgree']='0';
						$cc['student_name']=$cc['student_name'].'(不用授權)';
					}
					$cc['student_idno']=$studentRow['cn'];
					$cc['id']=$d['id'];
				}
				
				if($student_id==$d['id']) {
					 $studentData=$cc;
					 $cc['isChecked']='selected';
				} else {
					$cc['isChecked']='';
				}
				$dataList[$d['id']]=$cc;

			}
			if(empty($studentData)) {
				foreach ($dataList as $dd) {
					 $studentData=$dd;
					 $student_id=$dd['id'];
					 break;
				}
			}

			if($studentData) {
				//取該學生授權資料
				$agreeAll = OauthThirdappStudent::where('parent_idno',$userNow->idno)->where('type','1')->where('student_idno',$studentData['student_idno'])->first();
				$agreeList = OauthThirdappStudent::where('parent_idno',$userNow->idno)->where('type','0')->where('student_idno',$studentData['student_idno'])->get();
			}	
		}
		//取得要12的授權第三方
		$apps=[];
		$thirdappList=Thirdapp::where('authyn','Y')->get();
		if($thirdappList) {
			foreach ($thirdappList as $t) {
				$apps[$t->id]['id'] = $t->id;
				$apps[$t->id]['entry'] = $t->entry;
				$apps[$t->id]['background'] = $t->background;
				$apps[$t->id]['agree']='0';
				if($agreeList) {
					foreach ($agreeList as $a) {
						if($a['thirdapp_id']== $t->id)  $apps[$t->id]['agree']='1';
					}
				}
				$sc = $t->scope;
				$apps[$t->id]['scope_list']='';
				if(is_string ($sc) && $sc != ''){
					$ss = explode(" ", $sc);
					$scopes=DB::table('oauth_scope_field')->select('field_cname')->distinct()->whereIn('scope', $ss)->get();
					foreach ($scopes as $s) {
						if($apps[$t->id]['scope_list']!='') $apps[$t->id]['scope_list']=$apps[$t->id]['scope_list'] . ", ";
						$apps[$t->id]['scope_list']=$apps[$t->id]['scope_list'] . $s->field_cname;
					}
				} else  {
					$apps[$t->id]['scope_list']="";
				}				
			}
		}
		return view('parents.connectchildrenauth', ['dataList' => $dataList,'apps' => $apps, 'agreeList' => $agreeList, 'agreeAll' => $agreeAll,'student' => $studentData]);		
	}
	
	public function authConnectChild(Request $request)
    {
		if($request->get('student')=='') {
			return redirect()->back()->with("error","無法進行更新，可能您選擇的是非12歲以下學生，謝謝！")->withInput();
		} 

		$studentData = StudentParentRelation::where('id',$request->get('student'))->first();
		$res=OauthThirdappStudent::where('student_idno',$studentData->student_idno)->where('parent_idno',$userNow->idno)->delete();
		if($request->get('agreeAll')=='1') {
			$obj  = new \App\OauthThirdappStudent();
			$obj->student_idno=$studentData->student_idno;
			$obj->parent_idno=$userNow->idno;
			$obj->type='1';
			$obj->save();
		} else  {
			$agreeList = $request->get('agree');
			if(!empty($agreeList)) {
				foreach($agreeList as $a){
					if($a!='') {
						$obj  = new \App\OauthThirdappStudent();
						$obj->student_idno=$studentData->student_idno;
						$obj->parent_idno=$userNow->idno;
						$obj->type='0';
						$obj->thirdapp_id=$a;
						$obj->save();
					}	
				}
			}
		}
		
		return redirect()->route('parents.showConnectChildrenAuthForm')->with("success","授權更新成功！")->with("student",$request->get('student'));
	}		

	public function connectChildQRcode(Request $request)
	{
		$openldap = new LdapServiceProvider();
	  $qrcodeData = $request->session()->pull('qrcodeObject'); //StudentParentsQrcode
	  //用姓名 座號 位置 於LDAP找學生
	  $students = $openldap->findUsers("(&(displayName=$qrcodeData->std_name)(tpSeat=$qrcodeData->std_seat)(employeeType=學生)(tpClass=$qrcodeData->std_cls))", ["entryUUID","inetUserStatus","uid","cn","displayName","tpClass","tpSeat","o","birthDate"]);
	  if($students) {
		foreach ($students as $stu) {
			//用stu idno id  + 父名找關連
			$stuParentData = StudentParentData::where('parent_name',$qrcodeData->par_name)
			->where('parent_relation',$qrcodeData->par_rel)
			->where('student_idno',$stu['cn'])
			->where('id',$qrcodeData->dataid)
			//->where('student_birthday',substr($stu['birthDate'],0,8))
			->first();

			//綁定 家長ID check
			if($stuParentData) {
				if($stuParentData->status=='0') {
					//如父母有身分證就要再多核對						
					if($stuParentData->parent_idno!='') {
						if($stuParentData->parent_idno != $userNow->idno) {
							return redirect()->route('parents.listConnectChildren')->with("error","您帳號的身分證號與學生的監護人資料不符，請確認後再行綁定，謝謝！");
						}
					}	
					//進行綁定
					$parentRelation  = new \App\StudentParentRelation();
					$parentRelation->student_idno=$stu['cn'];
					//$parentRelation->student_birthday = substr($stu['birthDate'],0,8);
					$parentRelation->parent_name=$qrcodeData->par_name;
					$parentRelation->parent_idno=$userNow->idno;
					$parentRelation->parent_relation=$qrcodeData->par_rel;
					$parentRelation->status='1';
					$parentRelation->save();

					//更新student_parent_data
					$stuParentData->status='1';
					$stuParentData->save();	
					return redirect()->route('parents.listConnectChildren')->with("success","家長學生關連綁定成功！");
				} else {
					return redirect()->route('parents.listConnectChildren')->with("error","該筆家長學生關連資料已綁定過，謝謝！");
				}
			} else {
				return redirect()->route('parents.listConnectChildren')->with("error","綁定學生家長對應資料不符，請與學校確認後再行綁定，謝謝！");
			}
			break;
		}
	  } else {
		return redirect()->route('parents.listConnectChildren')->with("error","該學生資料不存在於本系統，請與學校確認後再行綁定，謝謝！");
	  }
	}

	public function connectApply(Request $request)
	{
		$idno = $request->get('idno');
		$dc = $request->get('dc');
		$stdno = $request->get('stdno');
		$birth = $request->get('birth');
		$pidno = Auth::user()->idno;
		$rtype = $request->get('rtype');
		$pname = $request->get('pname');
		$email = $request->get('email');
		$mobile = $request->get('mobile');

		$openldap = new LdapServiceProvider();
		$schid = $openldap->getOrgID($request->get('dc'));
		$chkidno = new idno();

		$entry = $openldap->getUserEntry($idno);
		if($entry)
			$st = $openldap->getUserData($entry);

		if(!isset($st) || empty($st) || ((is_array($st['employeeType']) && !in_array('學生',$st['employeeType'])) || (is_string($st['employeeType']) && $st['employeeType'] != '學生')) || $st['employeeNumber'] != $stdno || substr($st['birthDate'],0,8) != $birth || $st['o'] != $dc || empty($schid))
			return '{"error":"學生資料錯誤"}';

		if(!$chkidno->passes(null,$pidno) || ($rtype != '父親' && $rtype != '母親' && $rtype != '監護人') || empty($pname))
			return '{"error":"家長資料錯誤"}';

		$data = [];
		if(empty($email)){
			$data['email'] = 'eMail信箱為必填！';
		}else if(count(explode('@',$email)) != 2 || strlen($email) < 8 || substr($email,-1) == '.' || count(explode('.',explode('@',$email)[1])) < 2){
			$data['email'] = 'eMail信箱格式不正確！';
		}

		if(empty($mobile)){
			$data['mobile'] = '電話號碼為必填！';
		}

		if(count($data) > 0)
			return json_encode($data, JSON_UNESCAPED_UNICODE);

		$sp = StudentParentRelation::where('student_idno',$idno)->where('parent_idno',$pidno)->get();
		if(count($sp) > 0)
			return '{"error":"輸入的身分證字號已建立過親子連結"}';

		//是否資料在DB有
		$stuParentData = StudentParentData::where('parent_name',$pname)
			->where('school_id',$schid)
			->where('student_idno',$idno)
			//->where('student_id',$stdno)//mysql裡的學號棄用,比過ldap就好
			//->where('student_birthday',$birth)//mysql裡的生日棄用,比過ldap就好
			->first();

		$askApply = false;

		if($stuParentData) {
			//如父母有身分證就要再多核對
			if($stuParentData->parent_idno != '' && $stuParentData->parent_idno != $userNow->idno)
				$askApply = true;
		} else {
			$apply = \App\StudentParentApply::where('student_idno',$idno)->where('parent_idno',$pidno)->where('status','0')->first();
			if(!empty($apply))
				return '{"error":"已有待審核的申請存在，不可以重複送出申請"}';
			$askApply = true;
		}

		if($askApply){
			/*
			$entry = $openldap->getUserEntry($pidno);
			$pdata = $openldap->getUserData($entry);

			$accounts = [];
			if (is_array($pdata['uid'])) $accounts = $pdata['uid'];
			else $accounts[] = $pdata['uid'];
			$emailLogin = array_key_exists('mail',$pdata) && in_array($pdata['mail'],$accounts);
			$mobileLogin = array_key_exists('mobile',$pdata) && in_array($pdata['mobile'],$accounts);
			*/
			$user = Auth::user();

			//$accounts = $openldap->getUserAccounts($pidno);
			/*
			$userinfo = array();

			if(!array_key_exists('mail',$pdata)){
				$userinfo['mail'] = $email;
				$user->email = $email;
			}else if ($pdata['mail'] != $email){
				if(!$openldap->emailAvailable($pidno, $email))
					return '{"error":"您輸入的電子郵件已經被別人使用！"}';
				$userinfo['mail'] = $email;
				$user->email = $email;
			}

			if(!array_key_exists('mobile',$pdata)){
				$userinfo['mobile'] = $mobile;
				$user->mobile = $mobile;
			}else if ($pdata['mobile'] != $mobile){
				if(!$openldap->mobileAvailable($pidno, $mobile))
					return '{"error":"您輸入的手機號碼已經被別人使用！"}';
				$userinfo['mobile'] = $mobile;
				$user->mobile = $mobile;
			}
			*/

			if(!$openldap->emailAvailable($pidno, $email))
				return '{"error":"您輸入的電子郵件已經被別人使用！"}';
			if(!$openldap->mobileAvailable($pidno, $mobile))
				return '{"error":"您輸入的手機號碼已經被別人使用！"}';

			if($user->email != $email || $user->mobile != $mobile){
				$user->email = $email;
				$user->mobile = $mobile;

				$info = \App\ParentsInfo::where('cn',$pidno)->first();
				if($info){
					$info->mail = $email;
					$info->mobile = $mobile;
				}else{
					return '{"error":"找不到個人資料！"}';
				}
			}

			$a = new \App\StudentParentApply();
			$a->school_id = $schid;
			$a->student_idno = $idno;
			$a->parent_idno = $pidno;
			$a->parent_relation = $rtype;
			$a->parent_name = $pname;
			$a->parent_email = $email;
			$a->parent_mobile = $mobile;
			$a->save();

			$message = '已送出申請資料，請等待導師審核';

			if(isset($info)){
				$user->save();
				$info->save();
			}

			$filter = '(&(o='.$dc.')(tpTutorClass='.$st['tpClass'].'))';
			$tutor = $openldap->findUsers($filter, ["displayName","mail"]);

			if(!empty($tutor)){
				$m = $tutor[0]['mail'];

				if(empty($m)){
					$message .= '<br/>學生的導師未設定email，無法發送通知';
				}else if(!filter_var($m, FILTER_VALIDATE_EMAIL)){
					$message .= '<br/>學生的導師email設定錯誤，無法發送通知';
				}else{
					try{
						$text = $tutor[0]['displayName'].' 老師您好'.PHP_EOL.PHP_EOL.'您班上學生 '.$st['displayName'].' 的'.$rtype.'於 '.Carbon::now()->format('Y-m-d H:i:s').' 送出了一份建立親子連結的申請'.PHP_EOL.'請您抽空登入<臺北市教育人員單一身分驗證服務>審核';
						\Mail::raw($text, function($message) use ($m)
						{
							$message->from(env('MAIL_USERNAME', ''), '臺北市教育人員單一身分驗證服務');
							$message->to($m)->subject('臺北市教育人員單一身分驗證服務-學生家長建立親子連結申請');
						});

						$message .= '<br/>已發送申請通知mail給學生的導師';
					}catch (\Exception $e){
						$message .= '<br/>發送申請通知mail給學生的導師時發生錯誤';
					}
				}
			}else{
				$message .= '<br/>找不到學生的導師，無法發送通知';
			}

			\Session::flash('success', $message);
			return '{"success":"'.$message.'"}';
		}else{
			return '{"error":"輸入的家長資料錯誤，請重新操作親子連結服務功能"}';
		}

		return json_encode($data, JSON_UNESCAPED_UNICODE);
	}

}