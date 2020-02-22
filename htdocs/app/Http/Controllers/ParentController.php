<?php

namespace App\Http\Controllers;

use Auth;
use Log;
use Carbon\Carbon;
use App\User;
use App\PSLink;
use App\PSAuthorize;
use Laravel\Passport\Passport;
use Illuminate\Http\Request;
use App\Providers\SimsServiceProvider;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;

class ParentController extends Controller
{

	public function index()
	{
		$user = Auth::user();
		if (!($user->is_parent)) return redirect()->route('home');
		$idno = $user->idno;
		$kids = PSLink::where('parent_idno', $idno)->where('verified', 1)->orderBy('created_at','desc')->get();
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
			$k = array();
			$student_idno = $l->student_idno;
			$entry = $openldap->getUserEntry($student_idno);
			$data = $openldap->getUserData($entry);
			$school = $openldap->getOrgTitle($data['o']);
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
		$relations = [ '父子', '母子', '監護人' ];
		return view('parents.linkEdit', [ 'relations' => $relations ]);
	}
	
	public function applyLink(Request $request)
    {
		$validatedData = $request->validate([
            'idno' => ['required', 'string', 'size:10', new idno],
			'relation' => 'required|string',
		]);
		$alle = new SimsServiceProvider();
		$openldap = new LdapServiceProvider();
		$user = Auth::user();
		$idno = strtoupper($request->get('idno'));
		$relation = $request->get('relation');
		$student = $openldap->getUserEntry($idno);
		$data = $openldap->getUserData($student);
		$dc = $data['o'];
		$role = $data['employeeType'];
		$stdno = $data['employeeNumber'];
		if ($role != '學生') return back()->with("error","該身份證字號不屬於貴子弟所有！");
		$info = array();
		$info['parent_idno'] = $user->idno;
		$info['student_idno'] = $idno;
		$info['relation'] = $relation;
		$org = $openldap->getOrgEntry($dc);
		$odata = $openldap->getOrgData($org);
		if (!empty($odata['tpSims'])) $sims = $odata['tpSims'];
		if (isset($sims) && $sims == 'alle') {
			$uno = $odata['tpUniformNumbers'];
			$parents = $alle->ps_call('student_parents_info', [ 'sid' => $uno, 'stdno' => $stdno ]);
			$match = false;
			foreach ($parents as $p) {
				if ($p->name == $user->name) {
					$reason = array();
					if ($user->mobile && empty($p->telephone)) 
						$reason[] = '學籍資料缺家長手機號碼';
					elseif ($user->mobile != $p->telephone)
						$reason[] = '手機號碼不吻合';
					if ($p->relation == $relation) $reason[] = '親子關係不吻合';
					if (empty($reason)) $match = true;
					break;
				}
			}
			if ($match) {
				$info['verified'] = 1;
				$info['verified_time'] = time();
			} else {
				$info['denyReason'] = implode('、', $reason);
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

	public function listAuthProxy(Request $request)
	{
		$openldap = new LdapServiceProvider();
		$idno = Auth::user()->idno;
		$links = PSLink::where('parent_idno', $idno)->orderBy('created_at','desc')->get();
		$idnos = array();
		$kids = array();
		foreach ($links as $l) {
			$student_idno = $l->student_idno;
			$idnos[] = $student_idno;
			$entry = $openldap->getUserEntry($student_idno);
			$data = $openldap->getUserData($entry);
			$age = Carbon::today()->subYears(13);
			$str = $data['birthDate'];
			$born = Carbon::createFromDate(substr($str,0,4), substr($str,4,2), substr($str,6,2), 'Asia/Taipei');
			if ($born > $age) {
				$kids[$student_idno] = $data['displayName'];
			}
		}
		$myidno = $request->get('myidno');
		if (!$myidno) $myidno = $idnos[0];
		$authorizes = PSAuthorize::where('student_idno', $myidno)->get();
		return view('parents.listAuthProxy', [ 'myidno' => $myidno, 'authorizes' => $authorizes, 'kids' => $kids, 'trust_level' => Config::get('app.trust_level') ]);
	}

	public function showAuthProxyForm(Request $request)
    {
		$openldap = new LdapServiceProvider();
		$idno = Auth::user()->idno;
		$links = PSLink::where('parent_idno', $idno)->orderBy('created_at','desc')->get();
		$idnos = array();
		$kids = array();
		foreach ($links as $l) {
			$student_idno = $l->student_idno;
			$idnos[] = $student_idno;
			$entry = $openldap->getUserEntry($student_idno);
			$data = $openldap->getUserData($entry);
			$age = Carbon::today()->subYears(13);
			$str = $data['birthDate'];
			$born = Carbon::createFromDate(substr($str,0,4), substr($str,4,2), substr($str,6,2), 'Asia/Taipei');
			if ($born > $age) {
				$kids[$student_idno] = $data['displayName'];
			}
		}
		$myidno = $request->get('myidno');
		if (!$myidno) $myidno = $idnos[0];
		$apps = Passport::client()->all();
		$agreeAll = PSAuthorize::where('student_idno', $myidno)->where('client_id', '*')->first();
		return view('parents.authProxyForm', [ 'myidno' => $myidno, 'kids' => $kids, 'apps' => $apps, 'agreeAll' => $agreeAll ]);		
	}

	public function updateAuthProxy(Request $request, $id)
    {
		$auth = PSAuthorize::find($id);
		$auth->trust_level = $request->get('level');
		$auth->save();
		return back()->with('success', '已為您變更信任等級！');
	}

	public function removeAuthProxy(Request $request, $id)
    {
		$auth = PSAuthorize::find($id)->delete();
		return back()->with('success', '已為您刪除代理授權設定！');
	}

	public function applyAuthProxy(Request $request)
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

	public function linkQRcode(Request $request)
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

}