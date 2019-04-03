<?php

namespace App\Jobs;

use Log;
use Config;
use Validator;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;
use App\Providers\LdapServiceProvider;
use App\Providers\SimsServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncAlle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    static private $dc = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dc = '')
    {
        if (!empty($dc)) self::$dc = $dc;
    }

    /**
     * Execute the job.
     *
     * @param  string $dc
     * @return void
     */
    public function handle()
    {
        $openldap = new LdapServiceProvider();
        $http = new SimsServiceProvider();
        $dc = self::$dc;
        $sid = $openldap->getOrgID($dc);

        //sync classes
		$org_classes = $openldap->getOus($dc, '教學班級');
		$classes = $http->ps_getClasses($sid);
		if ($classes) {
			foreach ($classes as $class) {
				if (!empty($org_classes))
					for ($i=0;$i<count($org_classes);$i++) {
						if ($class->clsid == $org_classes[$i]->ou) array_splice($org_classes, $i, 1);
					}
				$class_entry = $openldap->getOuEntry($dc, $class->clsid);
				if ($class_entry) {
					$result = $openldap->updateData($class_entry, [ 'description' => $class->clsname ]);
				} else {
					$info = array();
					$info['objectClass'] = array('organizationalUnit','top');
					$info['businessCategory']='教學班級';
					$info['ou'] = $class->clsid;
					$info['description'] = $class->clsname;
					$info['dn'] = "ou=".$info['ou'].",dc=$dc,".Config::get('ldap.rdn');
					$result = $openldap->createEntry($info);
				}
			}
			if (!empty($org_classes))
				foreach ($org_classes as $org_class) {
					$class_entry = $openldap->getOuEntry($dc, $org_class->ou);
					$result = $openldap->deleteEntry($class_entry);
				}
		}

        //sync subjects
		$subjects = $http->ps_getSubjects($sid);
		if ($subjects) {
    		$org_subjects = $openldap->getSubjects($dc);
	    	for ($i=0;$i<count($org_subjects);$i++) {
		    	if (!in_array($org_subjects[$i]['description'], $subjects)) {
			    	$entry = $openldap->getSubjectEntry($dc, $org_subjects[$i]['tpSubject']);
				    $result = $openldap->deleteEntry($entry);
    				if ($result) {
	    				array_splice($org_subjects, $i, 1);
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
    			if (!in_array($subj_name, $subject_names)) {
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
			    }
            }
        }

        //sync teachers
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
		if (!empty($subjects))
			foreach ($subjects as $s) {
				$k = base64_encode($s['description']);
				$allsubject[$k] = $s['tpSubject'];
			}
		$teachers = $http->ps_getTeachers($sid);
		if (!empty($teachers)) {
			foreach ($teachers as $teaid) {
				$data = $http->ps_getTeacher($sid, $teaid);
				if (!empty($data['idno']) && !empty($data['name'])) {
					$idno = strtoupper($data['idno']);
					$validator = Validator::make(
						[ 'idno' => $idno ], [ 'idno' => new idno ]
					);
					if ($validator->fails()) {
						continue;
					}
					$user_entry = $openldap->getUserEntry($idno);
					$orgs = array();
					$units = array();
					$roles = array();
					$assign = array();
					$educloud = array();
					$role = '教師';
					if ($user_entry) {
						$original = $openldap->getUserData($user_entry);
						$os = array();
						if (!empty($original['o'])) {
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
						if (!empty($original['ou'])) {
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
						if (!empty($original['title'])) {
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
						if (!empty($original['tpTeachClass'])) {
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
						if (!empty($original['info'])) {
							if (is_array($original['info'])) {
								$educloud = $original['info'];
							} else {
								$educloud[] = $original['info'];
							}
							foreach ($educloud as $k => $c) {
								$i = (array) json_decode($c, true);
								if ($i['sid'] == $sid) unset($educloud[$k]);
							}
						}
						if (!empty($data['job_title'])) {
							foreach ($data['job_title'] as $job) {
								if (strpos($job, '校長')) $role = '校長';
								if (strpos($job, '工友')) $role = '職工';
								if (strpos($job, '警衛')) $role = '職工';
								if (strpos($job, '幹事')) $role = '職工';
								if (strpos($job, '員')) $role = '職工';
								if (strpos($job, '心')) $role = '職工';
								if (strpos($job, '護')) $role = '職工';
								$k = base64_encode($job);
								if (isset($allroles[$k]) && !empty($allroles[$k])) {
									$units[] = "$dc," . $allroles[$k]['ou'];
									$roles[] = "$dc," . $allroles[$k]['title'];
								}
							}
						}
						$educloud[] = json_encode(array("sid" => $sid, "role" => $role), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
						if (!empty($data['assign'])) {
							$classes = $data['assign'];
							foreach ($classes as $class => $subjects) {
								foreach ($subjects as $s) {
									$k = base64_encode($s);
									if (isset($allsubject[$k]) && !empty($allsubject[$k])) {
										$assign[] = "$dc,$class," . $allsubject[$k];
									}
								}
							}
						}
						$info = array();
						$info['o'] = array_values(array_unique($orgs));
						$info['ou'] = array_values(array_unique($units));
						$info['title'] = array_values(array_unique($roles));
						$info['info'] = array_values(array_unique($educloud));
						if (!empty($assign)) $info['tpTeachClass'] = array_values(array_unique($assign));
						if (!empty($data['class'])) $info['tpTutorClass'] = $data['class'];
						$info['inetUserStatus'] = 'active';
						$info['employeeType'] = $role;
						$info['employeeNumber'] = $teaid;
						$name = $this->guess_name($data['name']);
						$info['sn'] = $name[0];
						$info['givenName'] = $name[1];
						$info['displayName'] = $data['name'];
						if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
						if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'].'000000Z';
						if (!empty($data['address'])) $info['registeredAddress'] = $data['address'];
						if (!empty($data['mail'])) $info['mail'] = $data['mail'];
						if (!empty($data['tel'])) $info['mobile'] = $data['tel'];
						$result = $openldap->updateData($user_entry, $info);
					} else {
						$account = array();
						$account["uid"] = $dc.substr($idno, -9);
						$account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
						$account["objectClass"] = "radiusObjectProfile";
						$account["cn"] = $idno;
						$account["description"] = '從校務行政系統同步';
						$account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
						$acc_entry = $openldap->getAccountEntry($account["uid"]);
						if ($acc_entry) {
							unset($account['dn']);
							$result = $openldap->updateData($acc_entry, $account);
							if (!$result) {
								continue;
							}
						} else {
							$result = $openldap->createEntry($account);
							if (!$result) {
								continue;
							}
						}
						if (!empty($data['job_title'])) {
							foreach ($data['job_title'] as $job) {
								if (strpos($job, '校長')) $role = '校長';
								if (strpos($job, '工友')) $role = '職工';
								if (strpos($job, '警衛')) $role = '職工';
								if (strpos($job, '幹事')) $role = '職工';
								if (strpos($job, '員')) $role = '職工';
								if (strpos($job, '心')) $role = '職工';
								if (strpos($job, '護')) $role = '職工';
								$k = base64_encode($job);
								if (isset($allroles[$k]) && !empty($allroles[$k])) {
									$units[] = "$dc," . $allroles[$k]['ou'];
									$roles[] = "$dc," . $allroles[$k]['title'];
								}
							}
						}
						if (!empty($data['assign'])) {
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
						$info['dn'] = "cn=$idno,".Config::get('ldap.userdn');
						$info['objectClass'] = array('tpeduPerson', 'inetUser');
						$info['cn'] = $idno;
						$info["uid"] = $account["uid"];
						$info["userPassword"] = $account["userPassword"];
						$info['o'] = $dc;
						$info['ou'] = array_values(array_unique($units));
						$info['title'] = array_values(array_unique($roles));
						$info['info'] = json_encode(array("sid" => $sid, "role" => $role), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
						if (!empty($assign)) $info['tpTeachClass'] = array_values(array_unique($assign));
						if (!empty($data['class'])) $info['tpTutorClass'] = $data['class'];
						$info['inetUserStatus'] = 'active';
						$info['employeeType'] = $role;
						$info['employeeNumber'] = $teaid;
						$name = $this->guess_name($data['name']);
						$info['sn'] = $name[0];
						$info['givenName'] = $name[1];
						$info['displayName'] = $data['name'];
						if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
						if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'].'000000Z';
						if (!empty($data['address'])) $info['registeredAddress'] = $data['address'];
						if (!empty($data['mail'])) $info['mail'] = $data['mail'];
						if (!empty($data['tel'])) $info['mobile'] = $data['tel'];
						$result = $openldap->createEntry($info);
					}
				}
			}
			$filter = "(&(o=$dc)(!(employeeType=學生)))";
			$org_teachers = $openldap->findUsers($filter, [ 'cn', 'employeeNumber' ]);
			foreach ($org_teachers as $tea) {
				if (empty($tea['employeeNumber']) || !in_array($tea['employeeNumber'], $teachers)) {
					$user_entry = $openldap->getUserEntry($tea['cn']);
					$original = $openldap->getUserData($user_entry);
					$os = $orgs = array();
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
					$ous = $units = array();
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
					$titles = $roles = array();
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
					$tclass = $assign = array();
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
					$educloud = array();
					if (isset($original['info'])) {
						if (is_array($original['info'])) {
							$educloud = $original['info'];
						} else {
							$educloud[] = $original['info'];
						}
						foreach ($educloud as $k => $c) {
							$i = (array) json_decode($c, true);
							if ($i['sid'] == $sid) unset($educloud[$k]);
						}
					}
					$info = array();
					$info['o'] = array_values($orgs);
					$info['ou'] = array_values($units);
					$info['title'] = array_values($roles);
					$info['tpTeachClass'] = array_values($assign);
					$info['info'] = array_values($educloud);;
					$info['tpTutorClass'] = [];
					$info['inetUserStatus'] = 'deleted';
					$openldap->updateData($user_entry, $info);
				}
			}
        }
        
        //sync students
		$classes = $http->ps_getClasses($sid);
		if ($classes) {
			foreach ($classes as $class) {
                $clsid = $class->clsid;
                $students = $http->ps_getStudents($sid, $clsid);
                if (!empty($students)) {
                    foreach ($students as $stdno) {
                        $data = $http->ps_getStudent($sid, $stdno);
                        if (isset($data['idno'])) {
                            $idno = strtoupper($data['idno']);
                            $validator = Validator::make(
                                [ 'idno' => $idno ], [ 'idno' => new idno ]
                            );
                            if ($validator->fails()) {
                                continue;
                            }
                            $user_entry = $openldap->getUserEntry($idno);
                            if ($user_entry) {
                                $result = $openldap->updateAccounts($user_entry, [ $dc.$stdno ]);
                                if (!$result) {
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
                                if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
                                if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'].'000000Z';
                                if (!empty($data['address'])) $info['registeredAddress'] = $data['address'];
                                if (!empty($data['mail'])) $info['mail'] = $data['mail'];
                                if (!empty($data['tel'])) $info['mobile'] = $data['tel'];
                                $result = $openldap->updateData($user_entry, $info);
                            } else {
                                $account = array();
                                $account["uid"] = $dc.$stdno;
                                $account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
                                $account["objectClass"] = "radiusObjectProfile";
                                $account["cn"] = $idno;
                                $account["description"] = '從校務行政系統同步';
                                $account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
                                $acc_entry = $openldap->getAccountEntry($account["uid"]);
                                if ($acc_entry) {
                                    unset($account['dn']);
                                    $result = $openldap->updateData($acc_entry, $account);
                                    if (!$result) {
                                        continue;
                                    }
                                } else {
                                    $result = $openldap->createEntry($account);
                                    if (!$result) {
                                        continue;
                                    }
                                }
                                $info = array();
                                $info['dn'] = "cn=$idno,".Config::get('ldap.userdn');
                                $info['objectClass'] = array('tpeduPerson', 'inetUser');
                                $info['cn'] = $idno;
                                $info["uid"] = $account["uid"];
                                $info["userPassword"] = $account["userPassword"];
                                $info['o'] = $dc;
                                $info['inetUserStatus'] = 'active';
                                $info['info'] = json_encode(array("sid" => $sid, "role" => "學生"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                                $info['employeeType'] = '學生';
                                $info['employeeNumber'] = $stdno;
                                $info['tpClass'] = $clsid;
                                $info['tpClassTitle'] = $clsname;
                                $info['tpSeat'] = (int) $data['seat'];
                                $name = $this->guess_name($data['name']);
                                $info['sn'] = $name[0];
                                $info['givenName'] = $name[1];
                                $info['displayName'] = $data['name'];
                                if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
                                if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'].'000000Z';
                                if (!empty($data['address'])) $info['registeredAddress'] = $data['address'];
                                if (!empty($data['mail'])) $info['mail'] = $data['mail'];
                                if (!empty($data['tel'])) $info['mobile'] = $data['tel'];
                                $result = $openldap->createEntry($info);
                            }
                        }
                    }
                    $filter = "(&(o=$dc)(tpClass=$clsid))";
                    $org_students = $openldap->findUsers($filter, [ 'cn', 'employeeNumber' ]);
                    foreach ($org_students as $stu) {
                        if (empty($stu['employeeNumber']) || !in_array($stu['employeeNumber'], $students)) {
                            $user_entry = $openldap->getUserEntry($stu['cn']);
                            $openldap->updateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
                        }
                    }
                }
            }
        }
    }

    public function retryUntil() {
        return now()->addSeconds(5);
    }
}
