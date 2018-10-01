<?php

namespace App\Http\Controllers;

use Config;
use Validator;
use Auth;
use Illuminate\Http\Request;
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
		return view('admin.synctest', [ 'my_field' => $my_field, 'result' => $result ]);
    }

}
