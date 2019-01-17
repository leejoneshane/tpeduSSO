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
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "(&(st=$area)(businessCategory=國民小學))";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$sid = $openldap->getOrgID($dc);
		$my_field = $request->get('field');
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
		return view('admin.ps_synctest', [ 'my_field' => $my_field, 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc, 'grade' => $grade, 'subjid' => $subjid, 'clsid' => $clsid, 'teaid' => $teaid, 'stdno' => $stdno, 'isbn' => $isbn, 'result' => $result ]);
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
			$messages[] = "無法同步班級資訊：". $http->error();
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
					$messages[] = "subject=". $org_subjects[$i]['tpSubject'] ." 已經不再使用，但無法刪除：". $http->error();
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
		$messages[] = "開始進行同步";
		$allroles = array();
		$ous = $openldap->getOus($dc, '行政部門');
		if (!empty($ous)) {
			foreach ($ous as $ou) {
				$ou_id = $ou->ou;
				$uname = $ou->description;
				$info = $openldap->getRoles($dc, $ou_id);
				if (!empty($info)) {
					foreach ($info as $i) {
						$k = base64_encode($i->description);
						$allroles[$k]['ou'] = $ou_id;
						$allroles[$k]['title'] = "$ou_id,$i->cn";
					}
				}
			}
		}
		$allsubject = array();
		$subjects = $openldap->getSubjects($dc);
		foreach ($subjects as $s) {
			$k = base64_encode($s['description']);
			$allsubject[$k] = $s['tpSubject'];
		}
		$teachers = $http->getTeachers($sid);
		if (empty($teachers)) {
			$messages[] = "查無教師清單，因此無法同步！";
		} else {
			foreach ($teachers as $teaid) {
				$data = $http->getTeacher($sid, $teaid);
				if (isset($data['idno'])) {
					$user_entry = $openldap->getUserEntry($data['idno']);
					$orgs = array();
					$units = array();
					$roles = array();
					$assign = array();
					$educloud = array();
					$role = '教師';
					if ($user_entry) {
						$original = $openldap->getUserData($user_entry);
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
						$orgs[] = $dc;
						if (isset($original['info'])) {
							if (is_array($original['info'])) {
								$educloud = $original['info'];
							} else {
								$educloud[] = $original['info'];
							}
							foreach ($educloud as $k => $c) {
								$i = json_decode($c, true);
								if ($i['sid'] == $sid) unset($educloud[$k]);
							}
						}
						if (isset($data['job_title'])) {
							foreach ($data['job_title'] as $job) {
								if (strpos($job, '校長')) $role = '校長';
								if (strpos($job, '工友')) $role = '職工';
								if (strpos($job, '警衛')) $role = '職工';
								if (strpos($job, '員')) $role = '職工';
								if (strpos($job, '心')) $role = '職工';
								if (strpos($job, '護')) $role = '職工';
								$k = base64_encode($job);
								if (isset($allroles[$k])) {
									$units[] = "$dc," . $allroles[$k]['ou'];
									$roles[] = "$dc," . $allroles[$k]['title'];
								}
							}
						}
						$educloud[] = json_encode(array("sid" => $sid, "role" => $role), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
						if (isset($data['assign'])) {
							$classes = $data['assign'];
							foreach ($classes as $class => $subjects) {
								foreach ($subjects as $s) {
									$k = base64_encode($s);
									if (isset($allsubject[$k])) {
										$assign[] = "$dc,$class," . $allsubject[$k];
									}
								}
							}
						}
						$info = array();
						$info['o'] = $orgs;
						$info['employeeType'] = $role;
						$info['ou'] = $units;
						$info['title'] = $roles;
						$info['info'] = $educloud;
						$info['tpTeachClass'] = $assign;
						if (!empty($data['class'])) $info['tpTutorClass'] = $data['class'];
						$info['inetUserStatus'] = 'active';
						$info['employeeNumber'] = $teaid;
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
							$messages[] = "cn=". $data['idno'] .",teaid=". $teaid .",name=". $data['name'] ." 資料及帳號更新完成！";
						} else {
							$messages[] = "cn=". $data['idno'] .",teaid=". $teaid .",name=". $data['name'] ." 無法更新教師資料：". $openldap->error();
						}
					} else {
						$account = array();
						$account["uid"] = $dc.substr($data['idno'], -9);
						$account["userPassword"] = $openldap->make_ssha_password(substr($data['idno'], -6));
						$account["objectClass"] = "radiusObjectProfile";
						$account["cn"] = $data['idno'];
						$account["description"] = '從校務行政系統同步';
						$account["dn"] = Config::get('ldap.authattr')."=".$account['uid'].",".Config::get('ldap.authdn');
						$acc_entry = $openldap->getAccountEntry($account["uid"]);
						if ($acc_entry) {
							unset($account['dn']);
							$result = $openldap->UpdateData($acc_entry, $account);
							if (!$result) {
								$messages[] = "cn=". $data['idno'] .",teaid=". $teaid .",name=". $data['name'] . "因為預設帳號無法更新，教師新增失敗！".$openldap->error();
								continue;
							}
						} else {
							$result = $openldap->createEntry($account);
							if (!$result) {
								$messages[] = "cn=". $data['idno'] .",teaid=". $teaid .",name=". $data['name'] . "因為預設帳號無法建立，教師新增失敗！".$openldap->error();
								continue;
							}
						}
						if (isset($data['job_title'])) {
							foreach ($data['job_title'] as $job) {
								if (strpos($job, '校長')) $role = '校長';
								if (strpos($job, '工友')) $role = '職工';
								if (strpos($job, '警衛')) $role = '職工';
								if (strpos($job, '員')) $role = '職工';
								if (strpos($job, '心')) $role = '職工';
								if (strpos($job, '護')) $role = '職工';
								$k = base64_encode($job);
								if (isset($allroles[$k])) {
									$units[] = "$dc," . $allroles[$k]['ou'];
									$roles[] = "$dc," . $allroles[$k]['title'];
								}
							}
						}
						if (isset($data['assign'])) {
							$classes = $data['assign'];
							foreach ($classes as $class => $subjects) {
								foreach ($subjects as $s) {
									$k = base64_encode($s);
									if (isset($allsubject[$k])) {
										$assign[] = "$dc,$class," . $allsubject[$k];
									}
								}
							}
						}
						$info = array();
						$info['dn'] = Config::get('ldap.userattr').'='.$data['idno'].','.Config::get('ldap.userdn');
						$info['objectClass'] = array('tpeduPerson', 'inetUser');
						$info['cn'] = $data['idno'];
						$info["uid"] = $account["uid"];
						$info["userPassword"] = $account["userPassword"];
						$info['o'] = $dc;
						$info['employeeType'] = $role;
						$info['ou'] = $units;
						$info['title'] = $roles;
						$info['info'] = json_encode(array("sid" => $sid, "role" => $role), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
						$info['tpTeachClass'] = $assign;
						if (!empty($data['class'])) $info['tpTutorClass'] = $data['class'];
						$info['inetUserStatus'] = 'active';
						$info['employeeNumber'] = $teaid;
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
							$messages[] = "cn=". $data['idno'] .",teaid=". $teaid .",name=". $data['name'] . "已經為您建立教師資料！";
						} else {
							$messages[] = "cn=". $data['idno'] .",teaid=". $teaid .",name=". $data['name'] . "教師新增失敗！".$openldap->error();
						}
					}
				} else {
					$messages[] = "cn=無,teaid=". $teaid .",name=". $data['name'] ." 查無身份證號無法同步：". $http->error();
				}
			}
			$filter = "(&(dc=$dc)(!(employeeType=學生)))";
			$org_teachers = $openldap->findUsers($filter, [ 'cn', 'employeeNumber' ]);
			foreach ($org_teachers as $tea) {
				if (!in_array($tea['employeeNumber'], $teachers)) {
					$user_entry = $openldap->getUserEntry($tea['cn']);
					$openldap->UpdateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
				}
			}
		}
		$messages[] = "同步完成！";
		return $messages;
	}

	public function ps_syncStudentHelp(Request $request, $dc)
    {
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$sid = $openldap->getOrgID($dc);
		$school = $openldap->getOrgEntry($dc);
		$category = $openldap->getOrgData($school, 'businessCategory');
		if ($request->isMethod('post')) {
			$clsid = $request->get('clsid');
			if (empty($clsid)) {
				$classes = $http->getClasses($sid);
				$temp = array();
				foreach ($classes as $c) {
					$temp[$c->clsid] = $c->clsname;
				}
				ksort($temp);
				$classes = $temp;
				$clsid = key($classes);
				$clsname = $classes[$clsid];
				unset($classes[$clsid]);
				$request->session()->put('classes', $classes);
			} else {
				$classes = $request->session()->pull('classes');
				$clsid = key($classes);
				$clsname = $classes[$clsid];
				unset($classes[$clsid]);
				if (!empty($classes)) $request->session()->put('classes', $classes);
			}
			$result = $this->ps_syncStudent($dc, $sid, $clsid, $clsname);
			if (!empty($classes)) {
				$nextid = key($classes);
				return view('admin.syncstudentinfo', [ 'category' => $category['businessCategory'], 'dc' => $dc, 'clsid' => $nextid, 'result' => $result ]);	
			} else {
				return view('admin.syncstudentinfo', [ 'category' => $category['businessCategory'], 'dc' => $dc, 'result' => $result ]);	
			}
		} else {
			return view('admin.syncstudentinfo', [ 'category' => $category['businessCategory'], 'dc' => $dc ]);	
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
		if ($request->isMethod('post')) {
			$dc = $request->get('dc');
			$clsid = $request->get('clsid');
			$sid = $openldap->getOrgID($dc);
			if (empty($clsid)) {
				$classes = $http->getClasses($sid);
				$temp = array();
				if ($classes) {
					foreach ($classes as $c) {
						$temp[$c->clsid] = $c->clsname;
					}
					ksort($temp);
					$classes = $temp;
					$clsid = key($classes);
					$clsname = $classes[$clsid];
					unset($classes[$clsid]);
					$request->session()->put('classes', $classes);	
				}
			} else {
				$classes = $request->session()->pull('classes');
				$clsid = key($classes);
				$clsname = $classes[$clsid];
				unset($classes[$clsid]);
				if (!empty($classes)) $request->session()->put('classes', $classes);
			}
			if ($clsid && $clsname) {
				$result = $this->ps_syncStudent($dc, $sid, $clsid, $clsname);
			} else {
				$result[] = '查無班級，因此無法取得學生清單！';
			}
			if (!empty($classes)) {
				$nextid = key($classes);
				return view('admin.syncstudent', [ 'dc' => $dc, 'clsid' => $nextid, 'result' => $result ]);	
			} else {
				return view('admin.syncstudent', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc, 'result' => $result ]);	
			}
		} else {
			return view('admin.syncstudent', [ 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => '' ]);	
		}	
	}
	
	public function ps_syncStudent($dc, $sid, $clsid, $clsname)
	{
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$messages[] = "開始進行同步";
		$students = $http->getStudents($sid, $clsid);
		if (empty($students)) {
			$messages[] = "班級：$clsname 沒有學生，因此無法同步！";
		} else {
			foreach ($students as $stdno) {
				$data = $http->getStudent($sid, $stdno);
				if (isset($data['idno'])) {
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
							$result = $openldap->updateAccounts($user_entry, [ $dc.$stdno ]);
							if (!$result) {
								$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] . "因為帳號無法更新，學生同步失敗！".$openldap->error();
								continue;
							}
							$info = array();
							$info['o'] = $dc;
							$info['employeeType'] = '學生';
							$info['inetUserStatus'] = 'active';
							$info['employeeNumber'] = $stdno;
							$info['tpClass'] = $clsid;
							$info['tpClassTitle'] = $clsname;
							$info['tpSeat'] = (int) $data['seat'];
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
						$acc_entry = $openldap->getAccountEntry($account["uid"]);
						if ($acc_entry) {
							unset($account['dn']);
							$result = $openldap->UpdateData($acc_entry, $account);
							if (!$result) {
								$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] . "因為預設帳號無法更新，學生新增失敗！".$openldap->error();
								continue;
							}
						} else {
							$result = $openldap->createEntry($account);
							if (!$result) {
								$messages[] = "cn=". $data['idno'] .",stdno=". $stdno .",name=". $data['name'] . "因為預設帳號無法建立，學生新增失敗！".$openldap->error();
								continue;
							}
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
						$info['tpClass'] = $clsid;
						$info['tpClassTitle'] = $clsname;
						$info['tpSeat'] = (int) $data['seat'];
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
					$messages[] = "cn=無,stdno=". $stdno ." 查無身份證號無法同步：". $http->error();
				}
			}
			$filter = "(&(dc=$dc)(tpClass=$clsid)(employeeType=學生))";
			$org_students = $openldap->findUsers($filter, [ 'cn', 'employeeNumber' ]);
			foreach ($org_students as $stu) {
				if (!in_array($stu['employeeNumber'], $students)) {
					$user_entry = $openldap->getUserEntry($stu['cn']);
					$openldap->UpdateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
				}
			}
		}
		$messages[] = "同步完成！";
		return $messages;
	}

	public function js_testForm(Request $request)
	{
		$areas = [ '中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區' ];
		$area = $request->get('area');
		if (empty($area)) $area = $areas[0];
		$filter = "(&(st=$area)(!(businessCategory=國民小學)))";
		$openldap = new LdapServiceProvider();
		$schools = $openldap->getOrgs($filter);
		$dc = $request->get('dc');
		$sid = $openldap->getOrgID($dc);
		$my_field = $request->get('field');
		$ou = $request->get('ou');
		$clsid = $request->get('clsid');
		$idno = $request->get('idno');
		$ym = $request->get('ym');
		$http = new SimsServiceProvider();
		$result = array();
		switch($my_field) {
			case 'schools_info':
				$result = $http->js_call($my_field);
				break;
			case 'school_info':
			case 'units_info':
			case 'classes_info':
			case 'subjects_info':
				$result = $http->js_call($my_field, [ 'sid' => $sid ]);
				break;
			case 'roles_info':
				$result = $http->js_call($my_field, [ 'sid' => $sid, 'ou' => $ou ]);
				break;
			case 'teachers_in_class':
			case 'students_in_class':
				$result = $http->js_call($my_field, [ 'sid' => $sid, 'clsid' => $clsid ]);
				break;
			case 'person_info':
				$result = $http->js_call($my_field, [ 'sid' => $sid, 'idno' => $idno ]);
				break;
			case 'person_change':
				$result = $http->js_call($my_field, [ 'sid' => $sid, 'year-month' => $ym ]);
				break;
		}
		return view('admin.js_synctest', [ 'my_field' => $my_field, 'area' => $area, 'areas' => $areas, 'schools' => $schools, 'dc' => $dc, 'ou' => $ou, 'clsid' => $clsid, 'idno' => $idno, 'ym' => $ym, 'result' => $result ]);
	}
	
	function guess_name($myname) {
		$len = mb_strlen($myname, "UTF-8");
		if ($len > 3) {
			return array(mb_substr($myname, 0, 2, "UTF-8"), mb_substr($myname, 2, null, "UTF-8"));
		} else {
			return array(mb_substr($myname, 0, 1, "UTF-8"), mb_substr($myname, 1, null, "UTF-8"));
		}
	}	

}
