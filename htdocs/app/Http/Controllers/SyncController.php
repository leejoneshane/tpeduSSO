<?php

namespace App\Http\Controllers;

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
				$result = $http->ps_call($my_field, [ '{sid}' => $sid ]);
				break;
			case 'classses_by_grade':
				$result = $http->ps_call($my_field, [ '{sid}' => $sid, '{grade}' => $grade ]);
				break;
			case 'subject_info':
				$result = $http->ps_call($my_field, [ '{sid}' => $sid, '{subjid}' => $subjid ]);
				break;
			case 'classs_info':
			case 'classs_schedule':
			case 'students_in_class':
			case 'leaders_in_class':
			case 'teachers_in_class':
			case 'subject_for_class':
			case 'class_lend_record':
				$result = $http->ps_call($my_field, [ '{sid}' => $sid, '{clsid}' => $clsid ]);
				break;
			case 'teacher_info':
			case 'teacher_schedule':
			case 'teacher_tutor_students':
			case 'subject_assign_to_teacher':
				$result = $http->ps_call($my_field, [ '{sid}' => $sid, '{teaid}' => $teaid ]);
				break;
			case 'student_info':
			case 'student_subjects_score':
			case 'student_domains_score':
			case 'student_attendance_record':
			case 'student_health_record':
			case 'student_parents_info':
				$result = $http->ps_call($my_field, [ '{sid}' => $sid, '{stdno}' => $stdno ]);
				break;
			case 'book_info':
				$result = $http->ps_call($my_field, [ '{sid}' => $sid, '{isbn}' => $isbn ]);
				break;
		}
		return view('admin.synctest', [ 'my_field' => $my_field, 'sid' => $sid, 'grade' => $grade, 'subjid' => $subjid, 'clsid' => $clsid, 'teaid' => $teaid, 'stdno' => $stdno, 'isbn' => $isbn, 'result' => $result ]);
	}
	
    public function ps_syncSeatForm(Request $request)
    {
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "(&(st=$area)(businessCategory=國民小學))";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$result = '';
		if ($dc) {
			$result = $this->ps_syncSeat($dc);
			return redirect()->back()->with("success", $result);
		} else
			return view('admin.syncseat', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc ]);
	}
	
	public function ps_syncSeat($dc)
	{
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$sid = $openldap->getOrgID($dc);
		$org_classes = $openldap->getOus($dc, '教學班級');
		$classes = $http->ps_call('classes_info', [ '{sid}' => $sid ]);
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
		} else {
			$messages[] = "無法同步班級資訊：". $http->ps_error();
		}
		$students = $openldap->findUsers("(&(o=$dc)(employeeType=學生))", ["cn", "o", "displayName", "employeeNumber", "tpClass", "tpSeat"]);
		$messages = array();
		foreach ($students as $stu) {
			$stdno = $stu['employeeNumber'];
			$data = $http->ps_call('student_info', [ '{sid}' => $sid, '{stdno}' => $stdno ]);
			if ($data) {
				$user_entry = $openldap->getUserEntry($stu['cn']);
				if (substr($data[0]->class, 0, 1) == 'Z') {
					$result = $openldap->updateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
					if ($result) {
						$messages[] = "cn=". $stu['cn'] .",stdno=". $stu['employeeNumber'] .",name=". $stu['displayName'] ." 已畢業，標註為刪除！";
					} else {
						$messages[] = "cn=". $stu['cn'] .",stdno=". $stu['employeeNumber'] .",name=". $stu['displayName'] ." 無法標註畢業學生：". $openldap->error();
					}
				} else {
					$result = $openldap->updateData($user_entry, [ 'tpClass' => (int)$data[0]->class, 'tpSeat' => (int)$data[0]->seat ]);
					if ($result) {
						$messages[] = "cn=". $stu['cn'] .",stdno=". $stu['employeeNumber'] .",name=". $stu['displayName'] ." 就讀班級座號變更為 ". $data[0]->class . $data[0]->seat;
					} else {
						$messages[] = "cn=". $stu['cn'] .",stdno=". $stu['employeeNumber'] .",name=". $stu['displayName'] ." 無法變更班級座號：". $openldap->error();
					}
				}
			} else {
				$messages[] = "cn=". $stu['cn'] .",stdno=". $stu['employeeNumber'] .",name=". $stu['displayName'] ." 無法同步：". $http->ps_error();
			}
		}
		return $messages;
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
		$result = '';
		if ($dc) {
			$result = $this->ps_syncSubject($dc);
			return redirect()->back()->with("success", $result);
		} else
			return view('admin.syncsubject', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc ]);
	}
	
	public function ps_syncSubject($dc)
	{
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$sid = $openldap->getOrgID($dc);
		$classes = $openldap->getOus($dc, '教學班級');
		$messages = array();
		$subjects = array();
		foreach ($classes as $class) {
			$data = $http->ps_call('subject_for_class', [ '{sid}' => $sid, '{clsid}' => $class->ou ]);
			if (isset($data[0]->subjects)) {
				$class_subjects = $data[0]->subjects;
				foreach (array_keys($class_subjects) as $subj_name) {
					if (!in_array($subj_name, $subjects)) $subjects[] = $subj_name;
				}
			} else {
				$messages[] = "ou=". $class->ou ." 無法取得班級配課資訊：". $http->ps_error();
			}
		}
		$org_subjects = $openldap->getSubjects($dc);
		for ($i=0;$i<count($org_subjects);$i++) {
			if (!in_array($org_subjects[$i]->description, $subjects)) {
				$entry = $openldap->getSubjectEntry($dc, $org_subjects[$i]->subject);
				$result = $openldap->deleteEntry($entry);
				if ($result) {
					array_splice($org_subjects, $i, 1);
					$messages[] = "subject=". $org_subjects[$i]->subject ." 已經刪除！";
				} else {
					$messages[] = "subject=". $org_subjects[$i]->subject ." 已經不再使用，但無法刪除：". $http->ps_error();
				}
			}
		}
		$subject_ids = array();
		$subject_names = array();
		foreach ($org_subjects as $subj) {
			$subject_ids[] = $subj->subject;
			$subject_names[] = $subj->description;
		}
		foreach ($subjects as $subj_name) {
			if (!in_array($subj_name, $subject_names)) {
				for ($j=1;$j<100;$j++) {
					$new_id = 'subj'. ($j<10) ? '0'.$j : $j;
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
				$result = $openldap->addEntry($info);
				if ($result) {
					$messages[] = "subject=". $new_id ." 已將科目名稱設定為：". $subj_name;
				} else {
					$messages[] = "subject=". $new_id ." 無法新增：". $openldap->error();
				}
			}
		}
		return $messages;
	}
}
