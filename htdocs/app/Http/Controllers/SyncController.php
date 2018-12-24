<?php

namespace App\Http\Controllers;

use Log;
use Config;
use Validator;
use Auth;
use Illuminate\Http\Request;
use App\Providers\LdapServiceProvider;
use App\Providers\SimsServiceProvider;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;

class SyncController extends Controller
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
        return view('sync');
    }
    
	public function ps_testForm(Request $request)
	{
		$my_field = $request->get('field');
		$sid = $request->get('sid');
		$grade = $request->get('grade');
		$subjid = $request->get('subjid');
		$clsid = $request->get('clsid');
		$teaid = $request->get('teaid');
		$stdno = $request->get('stdno');
		$isbn = $request->get('isbn');
		$http = new SimsServiceProvider();
		$result = array();
		switch($my_field) {
			case 'school_info':
			case 'department_info':
			case 'classes_info':
			case 'special_info':
			case 'calendar_info':
			case 'library_books':
			case 'teachers_info':
				$result = $http->ps_call($my_field, [ 'sid' => $sid ]);
				break;
			case 'classses_by_grade':
				$result = $http->ps_call($my_field, [ 'sid' => $sid, 'grade' => $grade ]);
				break;
			case 'subject_info':
				$result = $http->ps_call($my_field, [ 'sid' => $sid, 'subjid' => $subjid ]);
				break;
			case 'classs_info':
			case 'classs_schedule':
			case 'students_in_class':
			case 'leaders_in_class':
			case 'teachers_in_class':
			case 'subject_for_class':
			case 'class_lend_record':
				$result = $http->ps_call($my_field, [ 'sid' => $sid, 'clsid' => $clsid ]);
				break;
			case 'teacher_info':
			case 'teacher_detail':
			case 'teacher_schedule':
			case 'teacher_tutor_students':
			case 'subject_assign_to_teacher':
				$result = $http->ps_call($my_field, [ 'sid' => $sid, 'teaid' => $teaid ]);
				break;
			case 'student_info':
			case 'student_detail':
			case 'student_subjects_score':
			case 'student_domains_score':
			case 'student_attendance_record':
			case 'student_health_record':
			case 'student_parents_info':
				$result = $http->ps_call($my_field, [ 'sid' => $sid, 'stdno' => $stdno ]);
				break;
			case 'book_info':
				$result = $http->ps_call($my_field, [ 'sid' => $sid, 'isbn' => $isbn ]);
				break;
		}
		return view('admin.synctest', [ 'my_field' => $my_field, 'sid' => $sid, 'grade' => $grade, 'subjid' => $subjid, 'clsid' => $clsid, 'teaid' => $teaid, 'stdno' => $stdno, 'isbn' => $isbn, 'result' => $result ]);
	}
	
    public function ps_syncClassHelp(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$sid = $openldap->getOrgID($dc);
		$school = $openldap->getOrgEntry($dc);
		$category = $openldap->getOrgData($school, 'businessCategory');
		$result = array();
		if ($request->get('submit')) $result = $this->ps_syncClass($dc, $sid);
		return view('admin.syncclassinfo', [ 'category' => $category['businessCategory'], 'dc' => $dc, 'result' => $result ]);
	}
	
	public function ps_syncClassForm(Request $request)
	{
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "(&(st=$area)(businessCategory=國民小學))";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$sid = $openldap->getOrgID($dc);
		$result = array();
		if ($dc) $result = $this->ps_syncClass($dc, $sid);
		return view('admin.syncclass', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc, 'result' => $result ]);
	}
	
	public function ps_syncClass($dc, $sid)
	{
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$org_classes = $openldap->getOus($dc, '教學班級');
		$classes = $http->getClasses($sid);
		$messages[] = "開始進行同步";
		if ($classes) {
			foreach ($classes as $class) {
				for ($i=0;$i<count($org_classes);$i++) {
					if ($class->clsid == $org_classes[$i]->ou) array_splice($org_classes, $i, 1);
				}
				$class_entry = $openldap->getOuEntry($dc, $class->clsid);
				if ($class_entry) {
					$result = $openldap->updateData($class_entry, [ 'description' => $class->clsname ]);
					if ($result) {
						$messages[] = "ou=". $class->clsid ." 已將班級名稱變更為：". $class->clsname;
					} else {
						$messages[] = "ou=". $class->clsid ." 無法變更班級名稱：". $openldap->error();
					}
				} else {
					$info = array();
					$info['objectClass'] = 'organizationalUnit';
					$info['businessCategory']='教學班級';
					$info['ou'] = $class->clsid;
					$info['description'] = $class->clsname;
					$info['dn'] = "ou=".$info['ou'].",dc=$dc,".Config::get('ldap.rdn');
					$result = $openldap->createEntry($info);
					if ($result) {
						$messages[] = "ou=". $class->clsid ." 已經為您建立班級，班級名稱為：". $class->clsname;
					} else {
						$messages[] = "ou=". $class->clsid ." 班級建立失敗：". $openldap->error();
					}
				}
			}
			foreach ($org_classes as $org_class) {
				$class_entry = $openldap->getOuEntry($dc, $org_class->ou);
				$result = $openldap->deleteEntry($class_entry);
				if ($result) {
					$messages[] = "ou=". $org_class->ou ." 已經為您刪除班級，班級名稱為：". $org_class->description;
				} else {
					$messages[] = "ou=". $org_class->ou ." 班級刪除失敗：". $openldap->error();
				}
			}
			$messages[] = "同步完成！";
		} else {
			$messages[] = "無法同步班級資訊：". $http->ps_error();
		}
		return $messages;
	}

	public function ps_syncSubjectHelp(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$sid = $openldap->getOrgID($dc);
		$school = $openldap->getOrgEntry($dc);
		$category = $openldap->getOrgData($school, 'businessCategory');
		$result = array();
		if ($request->get('submit')) $result = $this->ps_syncSubject($dc, $sid);
		return view('admin.syncsubjectinfo', [ 'category' => $category['businessCategory'], 'dc' => $dc, 'result' => $result ]);
	}
	
    public function ps_syncSubjectForm(Request $request)
    {
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "(&(st=$area)(businessCategory=國民小學))";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$sid = $openldap->getOrgID($dc);
		$result = array();
		if ($dc) $result = $this->ps_syncSubject($dc, $sid);
		return view('admin.syncsubject', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc, 'result' => $result ]);
	}
	
	public function ps_syncSubject($dc, $sid)
	{
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$messages[] = "開始進行同步";
		$subjects = $http->getSubjects($sid);
		if (!$subjects) return ["無法從校務行政系統取得所有科目，請稍後再同步！"];
		$org_subjects = $openldap->getSubjects($dc);
		for ($i=0;$i<count($org_subjects);$i++) {
			if (!in_array($org_subjects[$i]['description'], $subjects)) {
				$entry = $openldap->getSubjectEntry($dc, $org_subjects[$i]['tpSubject']);
				$result = $openldap->deleteEntry($entry);
				if ($result) {
					array_splice($org_subjects, $i, 1);
					$messages[] = "subject=". $org_subjects[$i]['tpSubject'] ." 已經刪除！";
				} else {
					$messages[] = "subject=". $org_subjects[$i]['tpSubject'] ." 已經不再使用，但無法刪除：". $http->ps_error();
				}
			}
		}
		$subject_ids = array();
		$subject_names = array();
		foreach ($org_subjects as $subj) {
			$subject_ids[] = $subj['tpSubject'];
			$subject_names[] = $subj['description'];
		}
		foreach ($subjects as $subj_name) {
			if (in_array($subj_name, $subject_names)) {
				$messages[] = $subj_name ." 科目已存在，略過不處理！";
			} else {
				for ($j=1;$j<100;$j++) {
					$new_id = 'subj';
					$new_id .= ($j<10) ? '0'.$j : $j;
					if (!in_array($new_id, $subject_ids)) {
						$subject_ids[] = $new_id;
						break;
					}
				}
				$info = array();
				$info['objectClass'] = 'tpeduSubject';
				$info['tpSubject'] = $new_id;
				$info['description'] = $subj_name;
				$info['dn'] = "tpSubject=".$new_id.",dc=$dc,".Config::get('ldap.rdn');
				$result = $openldap->createEntry($info);
				if ($result) {
					$messages[] = "subject=". $new_id ." 已將科目名稱設定為：". $subj_name;
				} else {
					$messages[] = "subject=". $new_id ." 無法新增：". $openldap->error();
				}
			}
		}
		$messages[] = "同步完成！";
		return $messages;
	}

	public function ps_syncTeacherHelp(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$sid = $openldap->getOrgID($dc);
		$school = $openldap->getOrgEntry($dc);
		$category = $openldap->getOrgData($school, 'businessCategory');
		$result = array();
		if ($request->get('submit')) $result = $this->ps_syncTeacher($dc, $sid);
		return view('admin.syncteacherinfo', [ 'category' => $category['businessCategory'], 'dc' => $dc, 'result' => $result ]);
	}
	
    public function ps_syncTeacherForm(Request $request)
    {
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "(&(st=$area)(businessCategory=國民小學))";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$sid = $openldap->getOrgID($dc);		
		$result = array();
		if ($dc) $result = $this->ps_syncTeacher($dc, $sid);
		return view('admin.syncteacher', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc, 'result' => $result ]);
	}

	public function ps_syncTeacher($dc, $sid)
	{
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$data = $http->ps_call('teachers_info', [ 'sid' => $sid ]);
		$messages[] = "開始進行同步";
//not yet, 等待全誼修正 data api
		$messages[] = "同步完成！";
		return $messages;
	}

	public function ps_syncStudentHelp(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$school = $openldap->getOrgEntry($dc);
		$data = $openldap->getOrgData($school, ['tpUniformNumbers', 'businessCategory']);
		$sid = $data['tpUniformNumbers'];
		$category = $data['businessCategory'];
		if ($request->isMethod('post')) {
			$clsid = $request->get('clsid');
			if (empty($clsid)) {
				$classes = $http->getClasses($sid);
				$clsid = $classes[0]->clsid;
				unset($classes[0]);
				$request->session()->put('classes', $classes);
			} else {
				$classes = $request->session()->pull('classes');
				$clsid = $classes[0]->clsid;
				unset($classes[0]);
				if (!empty($classes)) $request->session()->put('classes', $classes);
			}
			$result = $this->ps_syncSeat($dc, $sid, $clsid);
			if (!empty($classes)) {
				$clsid = $classes[0]->clsid;
				return view('admin.syncstudentinfo', [ 'category' => $category, 'dc' => $dc, 'clsid' => $clsid, 'result' => $result ]);	
			} else {
				return view('admin.syncstudentinfo', [ 'category' => $category, 'dc' => $dc, 'result' => $result ]);	
			}
		} else {
			return view('admin.syncstudentinfo', [ 'category' => $category, 'dc' => $dc ]);	
		}
	}
	
    public function ps_syncStudentForm(Request $request)
    {
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$filter = "(&(st=$area)(businessCategory=國民小學))";
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$clsid = $request->get('clsid');
		if (empty($dc)) {
			return view('admin.syncstudent', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => '' ]);	
		} else {
			$sid = $openldap->getOrgID($dc);
			if (empty($clsid)) {
				$classes = $http->getClasses($sid);
				$clsid = $classes[0]->ou;
				unset($classes[0]);
				$request->session()->put('classes', $classes);
			} else {
				$classes = $request->session()->pull('classes');
				$clsid = $classes[0]->ou;
				unset($classes[0]);
				if (!empty($classes)) $request->session()->put('classes', $classes);
			}
			$result = $this->ps_syncSeat($dc, $sid, $clsid);
			if (!empty($classes)) {
				$clsid = $classes[0]->ou;
				return view('admin.syncstudent', [ 'dc' => $dc, 'clsid' => $clsid, 'result' => $result ]);	
			} else {
				return view('admin.syncstudent', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc, 'result' => $result ]);	
			}
		}	
	}
	
	public function ps_syncSeat($dc, $sid, $clsid)
	{
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$messages[] = "開始進行同步";
		$students = $http->getStudents($sid, $clsid);
		foreach ($students as $stdno) {
			$data = $http->getStudent($sid, $stdno);
			if ($data) {
				$user_entry = $openldap->getUserEntry($data['idno']);
				if ($user_entry) {
					if (substr($data['class'], 0, 1) == 'Z') {
						$result = $openldap->updateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
						if ($result) {
							$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] ." 已畢業，標註為刪除！";
						} else {
							$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] ." 無法標註畢業學生：". $openldap->error();
						}
					} else {
						$account = array();
						$account["uid"] = $dc.$stdno;
						$account["userPassword"] = $openldap->make_ssha_password(substr($data['idno'], -6));
						$account["objectClass"] = "radiusObjectProfile";
						$account["cn"] = $data['idno'];
						$account["description"] = '從校務行政系統同步';
						$account["dn"] = Config::get('ldap.authattr')."=".$account['uid'].",".Config::get('ldap.authdn');
						$result = $openldap->updateAccounts($entry, $accounts);
						if (!$result) {
							$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] . "因為帳號無法更新，學生同步失敗！".$openldap->error();
							continue;
						}
						$info = array();
						$info["uid"] = $account["uid"];
						$info["userPassword"] = $account["userPassword"];
						$info['o'] = $dc;
						$info['employeeType'] = '學生';
						$info['inetUserStatus'] = 'active';
						$info['employeeNumber'] = $stdno;
						$info['tpClass'] = $data['class'];
						$info['tpSeat'] = $data['seat'];
						$name = $this->guess_name($data['name']);
						$info['sn'] = $name[0];
						$info['givenName'] = $name[1];
						$info['displayName'] = $data['name'];
						$info['gender'] = (int) $data['gender'];
						$info['birthDate'] = $data['birthdate'].'000000Z';
						if (!empty($data['address'])) $info['registeredAddress'] = $data['address'];
						if (!empty($data['mail'])) $info['mail'] = $data['email'];
						if (!empty($data['tel'])) $info['mobile'] = $data['tel'];
						$result = $openldap->updateData($user_entry, $info);
						if ($result) {
							$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] ." 資料及帳號更新完成！";
						} else {
							$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] ." 無法更新學生資料：". $openldap->error();
						}
					}
				} else {
					$account = array();
					$account["uid"] = $dc.$stdno;
					$account["userPassword"] = $openldap->make_ssha_password(substr($data['idno'], -6));
					$account["objectClass"] = "radiusObjectProfile";
					$account["cn"] = $data['idno'];
					$account["description"] = '從校務行政系統同步';
					$account["dn"] = Config::get('ldap.authattr')."=".$account['uid'].",".Config::get('ldap.authdn');
					$result = $openldap->createEntry($account);
					if (!$result) {
						$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] . "因為預設帳號無法建立，學生新增失敗！".$openldap->error();
						continue;
					}
					$info = array();
					$info['dn'] = Config::get('ldap.userattr').'='.$data['idno'].','.Config::get('ldap.userdn');
					$info['objectClass'] = array('tpeduPerson', 'inetUser');
					$info['cn'] = $data['idno'];
					$info["uid"] = $account["uid"];
					$info["userPassword"] = $account["userPassword"];
					$info['o'] = $dc;
					$info['employeeType'] = '學生';
					$info['inetUserStatus'] = 'active';
					$info['info'] = json_encode(array("sid" => $sid, "role" => "學生"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
					$info['employeeNumber'] = $stdno;
					$info['tpClass'] = $data['class'];
					$info['tpSeat'] = $data['seat'];
					$name = $this->guess_name($data['name']);
					$info['sn'] = $name[0];
					$info['givenName'] = $name[1];
					$info['displayName'] = $data['name'];
					$info['gender'] = (int) $data['gender'];
					$info['birthDate'] = $data['birthdate'].'000000Z';
					if (!empty($data['address'])) $info['registeredAddress'] = $data['address'];
					if (!empty($data['mail'])) $info['mail'] = $data['email'];
					if (!empty($data['tel'])) $info['mobile'] = $data['tel'];
					$result = $openldap->createEntry($info);
					if ($result) {
						$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] . "已經為您建立學生資料！";
					} else {
						$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] . "學生新增失敗！".$openldap->error();
					}
				}
			} else {
				$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] ." 無法同步：". $http->ps_error();
			}
		}
		$messages[] = "同步完成！";
		return $messages;
	}

	function guess_name($myname) {
		$len = mb_strlen($myname, "UTF-8");
		if ($len > 3) {
			return array(mb_substr($myname, 0, 2, "UTF-8"), mb_substr($myname, 2, NULL, "UTF-8"));
		} else {
			return array(mb_substr($myname, 0, 1, "UTF-8"), mb_substr($myname, 1, NULL, "UTF-8"));
		}
	}	

}
