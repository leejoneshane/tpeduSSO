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
		$students = $openldap->findUsers("(&(o=$dc)(employeeType=學生))", ["cn", "displayName", "employeeNumber", "tpClass", "tpSeat"]);
		$messages = array();
		foreach ($students as $stu) {
			$stdno = $stu['employeeNumber'];
			$data = $http->ps_call('student_info', [ '{sid}' => $sid, '{stdno}' => $stdno ]);
			if ($data) {
				$user_entry = $openldap->getUserEntry($stu['cn']);
				$openldap->updateData($user_entry, [ 'tpClass' => $data[0]->class, 'tpSeat' => $data[0]->seat ]);
				$messages[] = "cn=". $stu['cn'] .",stdno=". $stu['employeeNumber'] .",name=". $stu['displayName'] ." 就讀班級座號變更為 ". $data[0]->class . $data[0]->seat;
			} else {
				$messages[] = "cn=". $stu['cn'] .",stdno=". $stu['employeeNumber'] .",name=". $stu['displayName'] ." 無法同步：". $http->ps_error();
			}
		}
		return $messages;
	}

}
