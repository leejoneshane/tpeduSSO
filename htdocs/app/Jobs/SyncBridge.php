<?php

namespace App\Jobs;

use Log;
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

class SyncBridge implements ShouldQueue
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

        //sync depatments
		$org_units = $openldap->getOus($dc, '行政部門');
		$units = $http->hs_getUnits($sid);
		if ($units) {
			foreach ($units as $unit) {
				for ($i=0;$i<count($org_units);$i++) {
					if ($unit->ou == $org_units[$i]->ou) array_splice($org_units, $i, 1);
				}
				$unit_entry = $openldap->getOuEntry($dc, $unit->ou);
				if ($unit_entry) {
					$result = $openldap->updateData($unit_entry, [ 'description' => $unit->name ]);
				} else {
					$info = array();
					$info['objectClass'] = array('organizationalUnit','top');
					$info['businessCategory']='行政部門';
					$info['ou'] = $unit->ou;
					$info['description'] = $unit->name;
					$info['dn'] = "ou=".$info['ou'].",dc=$dc,".config('ldap.rdn');
					$result = $openldap->createEntry($info);
				}
			}
			foreach ($org_units as $org_unit) {
				$unit_entry = $openldap->getOuEntry($dc, $org_unit->ou);
				$result = $openldap->deleteEntry($unit_entry);
			}
        }
        
        //sync classes
		$org_classes = $openldap->getOus($dc, '教學班級');
		$classes = $http->hs_getClasses($sid);
		if ($classes) {
			foreach ($classes as $clsid => $clsname) {
				for ($i=0;$i<count($org_classes);$i++) {
					if ($clsid == $org_classes[$i]->ou) array_splice($org_classes, $i, 1);
				}
				$class_entry = $openldap->getOuEntry($dc, $clsid);
				if ($class_entry) {
					$result = $openldap->updateData($class_entry, [ 'description' => $clsname ]);
				} else {
					$info = array();
					$info['objectClass'] = array('organizationalUnit','top');
					$info['businessCategory']='教學班級';
					$info['ou'] = $clsid;
					$info['description'] = $clsname;
					$info['dn'] = "ou=".$info['ou'].",dc=$dc,".config('ldap.rdn');
					$result = $openldap->createEntry($info);
				}
			}
			foreach ($org_classes as $org_class) {
				$class_entry = $openldap->getOuEntry($dc, $org_class->ou);
				$result = $openldap->deleteEntry($class_entry);
			}
        }
        
        // sync subjects
		$subjects = $http->hs_getSubjects($sid);
		if ($subjects) {
    		$org_subjects = $openldap->getSubjects($dc);
	    	for ($i=0;$i<count($org_subjects);$i++) {
		    	if (!in_array($org_subjects[$i]['description'], $subjects)) {
			    	$entry = $openldap->getSubjectEntry($dc, $org_subjects[$i]['tpSubject']);
				    $result = $openldap->deleteEntry($entry);
				}
			}
    		$subject_ids = array();
	    	$subject_names = array();
		    if (!empty($org_subjects)) {
			    foreach ($org_subjects as $subj) {
				    $subject_ids[] = $subj['tpSubject'];
				    $subject_names[] = $subj['description'];
			    }
		    }
		    foreach ($subjects as $subj_id => $subj_name) {
			    if (!in_array($subj_name, $subject_names)) {
				    $info = array();
    				$info['objectClass'] = 'tpeduSubject';
	    			$info['tpSubject'] = $subj_id;
		    		$info['description'] = $subj_name;
			    	$info['dn'] = "tpSubject=".$subj_id.",dc=$dc,".config('ldap.rdn');
				    $result = $openldap->createEntry($info);
                }
            }
        }
        //sync teachers
		$teachers = $http->hs_getTeachers($sid);
		if (!empty($teachers)) {
			foreach ($teachers as $k => $idno) {
				$idno = strtoupper($idno);
				$data = $http->hs_getPerson($sid, $idno);
				if ($data) {
					$validator = Validator::make(
						[ 'idno' => $idno ], [ 'idno' => new idno ]
					);
					if ($validator->fails()) {
						unset($teachers[$k]);
						continue;
					}
					$user_entry = $openldap->getUserEntry($idno);
					$orgs = array();
					$units = array();
					$roles = array();
					$assign = array();
					$educloud = array();
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
						$educloud[] = json_encode(array("sid" => $sid, "role" => $data['type']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
						if (!empty($data['ou'])) $units[] = "$dc," . $data['ou'];
						if (!empty($data['role'])) $roles[] = "$dc," . $data['ou'] . "," . $data['role'];
						if (!empty($data['tclass'])) {
							$classes = $data['tclass'];
							foreach ($classes as $class) {
								list($clsid, $subjid) = explode(',', $class);
								$subjid = 'subj'.$subjid;
								$assign[] = "$dc,$clsid,$subjid";
							}
						}
						$info = array();
						$info['o'] = array_values(array_unique($orgs));
						$info['ou'] = array_values(array_unique($units));
						$info['title'] = array_values(array_unique($roles));
						$info['info'] = array_values(array_unique($educloud));
						if (!empty($assign)) $info['tpTeachClass'] = array_values(array_unique($assign));
						$info['inetUserStatus'] = 'active';
						$info['employeeType'] = $data['type'];
						$name = $this->guess_name($data['name']);
						$info['sn'] = $name[0];
						$info['givenName'] = $name[1];
						$info['displayName'] = $data['name'];
						if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
						if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'];
						if (!empty($data['register'])) $info['registeredAddress'] = $data['register'];
						if (!empty($data['mail'])) {
							$validator = Validator::make(
								[ 'mail' => $data['mail'] ], [ 'mail' => 'email' ]
							);
							if ($validator->passes()) $info['mail'] = $data['mail'];
						}	
						$result = $openldap->updateData($user_entry, $info);
					} else {
						$account = array();
						$account["uid"] = $dc.substr($idno, -9);
						$account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
						$account["objectClass"] = "radiusObjectProfile";
						$account["cn"] = $idno;
						$account["description"] = '從校務行政系統同步';
						$account["dn"] = "uid=".$account['uid'].",".config('ldap.authdn');
						$acc_entry = $openldap->getAccountEntry($account["uid"]);
						if ($acc_entry) {
							unset($account['dn']);
							$result = $openldap->updateData($acc_entry, $account);
						} else {
							$result = $openldap->createEntry($account);
						}
						if (!empty($data['ou'])) $units[] = "$dc," . $data['ou'];
						if (!empty($data['role'])) $roles[] = "$dc," . $data['ou'] . "," . $data['role'];
						if (!empty($data['tclass'])) {
							$classes = $data['tclass'];
							foreach ($classes as $class) {
								list($clsid, $subjid) = explode(',', $class);
								$subjid = 'subj'.$subjid;
								$assign[] = "$dc,$clsid,$subjid";
							}
						}
						$info = array();
						$info['dn'] = "cn=$idno,".config('ldap.userdn');
						$info['objectClass'] = array('tpeduPerson', 'inetUser');
						$info['cn'] = $idno;
						$info["uid"] = $account["uid"];
						$info["userPassword"] = $account["userPassword"];
						$info['o'] = $dc;
						$info['ou'] = array_values(array_unique($units));
						$info['title'] = array_values(array_unique($roles));
						$info['info'] = json_encode(array("sid" => $sid, "role" => $data['type']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
						if (!empty($assign)) $info['tpTeachClass'] = array_values(array_unique($assign));
						$info['inetUserStatus'] = 'active';
						$info['employeeType'] = $data['type'];
						$name = $this->guess_name($data['name']);
						$info['sn'] = $name[0];
						$info['givenName'] = $name[1];
						$info['displayName'] = $data['name'];
						if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
						if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'];
						if (!empty($data['register'])) $info['registeredAddress'] = $data['register'];
						if (!empty($data['mail'])) {
							$validator = Validator::make(
								[ 'mail' => $data['mail'] ], [ 'mail' => 'email' ]
							);
							if ($validator->passes()) $info['mail'] = $data['mail'];
						}	
						$result = $openldap->createEntry($info);
					}
				}
			}
			$filter = "(&(o=$dc)(!(employeeType=學生)))";
			$org_teachers = $openldap->findUsers($filter, 'cn');
			foreach ($org_teachers as $tea) {
				if (!in_array($tea['cn'], $teachers)) {
					$user_entry = $openldap->getUserEntry($tea['cn']);
					$original = $openldap->getUserData($user_entry);
					$os = $orgs = array();
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
					$ous = $units = array();
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
					$titles = $roles = array();
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
					$tclass = $assign = array();
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
					$educloud = array();
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
		$classes = $http->hs_getClasses($sid);
		if ($classes) {
			foreach ($classes as $clsid => $clsname) {
        		$students = $http->hs_getStudents($sid, $clsid);
		        if (!empty($students)) {
        			foreach ($students as $k => $idno) {
		        		$idno = strtoupper($idno);
				        $validator = Validator::make(
        					[ 'idno' => $idno ], [ 'idno' => new idno ]
		        		);
				        if ($validator->fails()) {
		        			unset($students[$k]);
				        	continue;
        				}
		        		$data = $http->hs_getPerson($sid, $idno);
        				$user_entry = $openldap->getUserEntry($idno);
		        		if ($user_entry) {
				        	$result = $openldap->updateAccounts($user_entry, [ $dc.$data['stdno'] ]);
        					if (!$result) {
        						continue;
		        			}
        					$info = array();
		        			$info['o'] = $dc;
				        	$info['inetUserStatus'] = 'active';
        					$info['employeeType'] = '學生';
		        			$info['employeeNumber'] = $data['stdno'];
				        	$info['tpClass'] = $clsid;
        					$info['tpClassTitle'] = $clsname;
		        			$info['tpSeat'] = (int) $data['seat'];
				        	$name = $this->guess_name($data['name']);
        					$info['sn'] = $name[0];
		        			$info['givenName'] = $name[1];
				        	$info['displayName'] = $data['name'];
        					if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
		        			if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'];
				        	if (!empty($data['register'])) $info['registeredAddress'] = $data['register'];
        					if (!empty($data['mail'])) {
		        				$validator = Validator::make(
				        			[ 'mail' => $data['mail'] ], [ 'mail' => 'email' ]
						        );
        						if ($validator->passes()) $info['mail'] = $data['mail'];
		        			}	
				        	$result = $openldap->updateData($user_entry, $info);
        				} else {
		        			$account = array();
				        	$account["uid"] = $dc.$data['stdno'];
        					$account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
		        			$account["objectClass"] = "radiusObjectProfile";
				        	$account["cn"] = $idno;
        					$account["description"] = '從校務行政系統同步';
		        			$account["dn"] = "uid=".$account['uid'].",".config('ldap.authdn');
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
		        			$info['dn'] = "cn=$idno,".config('ldap.userdn');
				        	$info['objectClass'] = array('tpeduPerson', 'inetUser');
        					$info['cn'] = $idno;
		        			$info["uid"] = $account["uid"];
				        	$info["userPassword"] = $account["userPassword"];
        					$info['o'] = $dc;
		        			$info['inetUserStatus'] = 'active';
				        	$info['info'] = json_encode(array("sid" => $sid, "role" => "學生"), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        					$info['employeeType'] = '學生';
		        			$info['employeeNumber'] = $data['stdno'];
				        	$info['tpClass'] = $clsid;
        					$info['tpClassTitle'] = $clsname;
		        			$info['tpSeat'] = (int) $data['seat'];
				        	$name = $this->guess_name($data['name']);
        					$info['sn'] = $name[0];
		        			$info['givenName'] = $name[1];
				        	$info['displayName'] = $data['name'];
        					if (!empty($data['gender'])) $info['gender'] = (int) $data['gender'];
		        			if (!empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'];
				        	if (!empty($data['register'])) $info['registeredAddress'] = $data['register'];
        					if (!empty($data['mail'])) {
		        				$validator = Validator::make(
				        			[ 'mail' => $data['mail'] ], [ 'mail' => 'email' ]
						        );
        						if ($validator->passes()) $info['mail'] = $data['mail'];
		        			}	
				        	$result = $openldap->createEntry($info);
        				}
		        	}
        			$filter = "(&(o=$dc)(tpClass=$clsid))";
		        	$org_students = $openldap->findUsers($filter, 'cn');
			        foreach ($org_students as $stu) {
        				if (!in_array($stu['cn'], $students)) {
		        			$user_entry = $openldap->getUserEntry($stu['cn']);
				        	$openldap->updateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
				        }
			        }
                }
            }
        }
    }

	function guess_name($myname) {
		$len = mb_strlen($myname, "UTF-8");
		if ($len > 3) {
			return array(mb_substr($myname, 0, 2, "UTF-8"), mb_substr($myname, 2, null, "UTF-8"));
		} else {
			return array(mb_substr($myname, 0, 1, "UTF-8"), mb_substr($myname, 1, null, "UTF-8"));
		}
	}	

    public function retryUntil() {
        return now()->addSeconds(5);
    }
}
