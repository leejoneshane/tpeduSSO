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

class SyncOneplus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    private $dc = '';

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
    public function handle($dc = '')
    {
        $openldap = new LdapServiceProvider();
        $http = new SimsServiceProvider();
        $filter = "(tpSims=oneplus)";
        if (!empty(self::$dc)) $dc=self::$dc;
        if (empty($dc)) {
            $schools = $openldap->getOrgs($filter);
            if ($schools)
                foreach ($schools as $school) {
                    $dc = $school->o;
                    SyncOneplus::dispatch($dc);
                }
        } else {
            $org_classes = $openldap->getOus($dc, '教學班級');
            $classes = $http->js_getClasses($sid);
            if ($classes) {
                foreach ($classes as $clsid => $clsname) {
                    for ($i=0;$i<count($org_classes);$i++) {
                        if ($clsid == $org_classes[$i]->ou) array_splice($org_classes, $i, 1);
                    }
                    $class_entry = $openldap->getOuEntry($dc, $clsid);
                    if ($class_entry) {
                        $openldap->updateData($class_entry, [ 'description' => $clsname ]);
                    } else {
                        $info = array();
                        $info['objectClass'] = 'organizationalUnit';
                        $info['businessCategory']='教學班級';
                        $info['ou'] = $clsid;
                        $info['description'] = $clsname;
                        $info['dn'] = "ou=".$info['ou'].",dc=$dc,".Config::get('ldap.rdn');
                        $openldap->createEntry($info);
                    }
                }
                foreach ($org_classes as $org_class) {
                    $class_entry = $openldap->getOuEntry($dc, $org_class->ou);
                    $openldap->deleteEntry($class_entry);
                }
            }
            $subjects = $http->js_getSubjects($sid);
            if ($subjects) {
                $org_subjects = $openldap->getSubjects($dc);
                for ($i=0;$i<count($org_subjects);$i++) {
                    if (!in_array($org_subjects[$i]['description'], $subjects)) {
                        $entry = $openldap->getSubjectEntry($dc, $org_subjects[$i]['tpSubject']);
                        if ($openldap->deleteEntry($entry)) array_splice($org_subjects, $i, 1);
                    }
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
                    $info['dn'] = "tpSubject=".$subj_id.",dc=$dc,".Config::get('ldap.rdn');
                    $openldap->createEntry($info);
                }
            }
            $teachers = $http->js_getTeachers($sid);
            if ($teachers) {
                foreach ($teachers as $k => $idno) {
                    $idno = strtoupper($idno);
                    $data = $http->js_getPerson($sid, $idno);
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
                                    $i = (array) json_decode($c, true);
                                    if ($i['sid'] == $sid) unset($educloud[$k]);
                                }
                            }
                            $educloud[] = json_encode(array("sid" => $sid, "role" => $data['type']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                            if (isset($data['ou']) && !empty($data['ou'])) $units[] = "$dc," . $data['ou'];
                            if (isset($data['role']) && !empty($data['role'])) $roles[] = "$dc," . $data['ou'] . "," . $data['role'];
                            if (isset($data['tclass'])) {
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
                            if (isset($data['gender']) && !empty($data['gender'])) $info['gender'] = (int) $data['gender'];
                            if (isset($data['birthdate']) && !empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'];
                            if (isset($data['register']) && !empty($data['register'])) $info['registeredAddress'] = $data['register'];
                            $openldap->updateData($user_entry, $info);
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
                                if (!$openldap->UpdateData($acc_entry, $account)) continue;
                            } else {
                                if (!$openldap->createEntry($account)) continue;
                            }
                            if (isset($data['ou']) && !empty($data['ou'])) $units[] = "$dc," . $data['ou'];
                            if (isset($data['role']) && !empty($data['role'])) $roles[] = "$dc," . $data['ou'] . "," . $data['role'];
                            if (isset($data['tclass'])) {
                                $classes = $data['tclass'];
                                foreach ($classes as $class) {
                                    list($clsid, $subjid) = explode(',', $class);
                                    $subjid = 'subj'.$subjid;
                                    $assign[] = "$dc,$clsid,$subjid";
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
                            $info['info'] = json_encode(array("sid" => $sid, "role" => $data['type']), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                            if (!empty($assign)) $info['tpTeachClass'] = array_values(array_unique($assign));
                            $info['inetUserStatus'] = 'active';
                            $info['employeeType'] = $data['type'];
                            $name = $this->guess_name($data['name']);
                            $info['sn'] = $name[0];
                            $info['givenName'] = $name[1];
                            $info['displayName'] = $data['name'];
                            if (isset($data['gender']) && !empty($data['gender'])) $info['gender'] = (int) $data['gender'];
                            if (isset($data['birthdate']) && !empty($data['birthdate'])) $info['birthDate'] = $data['birthdate'];
                            if (isset($data['register']) && !empty($data['register'])) $info['registeredAddress'] = $data['register'];
                            $openldap->createEntry($info);
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
                        if (empty($orgs)) $info['inetUserStatus'] = 'deleted';
                        $openldap->UpdateData($user_entry, $info);
                    }
                }
            }
            foreach ($classes as $clsid => $clsname) {
                $students = $http->js_getStudents($sid, $clsid);
                if ($students) {
                    foreach ($students as $k => $idno) {
                        $idno = strtoupper($idno);
                        $validator = Validator::make(
                            [ 'idno' => $idno ], [ 'idno' => new idno ]
                        );
                        if ($validator->fails()) {
                            unset($students[$k]);
                            continue;
                        }
                        $data = $http->js_getPerson($sid, $idno);
                        $user_entry = $openldap->getUserEntry($idno);
                        if ($user_entry) {
                            if (!$openldap->updateAccounts($user_entry, [ $dc.$data['stdno'] ])) continue;
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
                            $info['gender'] = (int) $data['gender'];
                            $info['birthDate'] = $data['birthdate'];
                            if (isset($data['register']) && !empty($data['register'])) $info['registeredAddress'] = $data['register'];
                            $openldap->updateData($user_entry, $info);
                        } else {
                            $account = array();
                            $account["uid"] = $dc.$data['stdno'];
                            $account["userPassword"] = $openldap->make_ssha_password(substr($idno, -6));
                            $account["objectClass"] = "radiusObjectProfile";
                            $account["cn"] = $idno;
                            $account["description"] = '從校務行政系統同步';
                            $account["dn"] = "uid=".$account['uid'].",".Config::get('ldap.authdn');
                            $acc_entry = $openldap->getAccountEntry($account["uid"]);
                            if ($acc_entry) {
                                unset($account['dn']);
                                if (!$openldap->UpdateData($acc_entry, $account)) continue;
                            } else {
                                if (!$openldap->createEntry($account)) continue;
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
                            $info['employeeNumber'] = $data['stdno'];
                            $info['tpClass'] = $clsid;
                            $info['tpClassTitle'] = $clsname;
                            $info['tpSeat'] = (int) $data['seat'];
                            $name = $this->guess_name($data['name']);
                            $info['sn'] = $name[0];
                            $info['givenName'] = $name[1];
                            $info['displayName'] = $data['name'];
                            $info['gender'] = (int) $data['gender'];
                            $info['birthDate'] = $data['birthdate'];
                            if (isset($data['register']) && !empty($data['register'])) $info['registeredAddress'] = $data['register'];
                            $openldap->createEntry($info);
                        }
                    }
                    $filter = "(&(o=$dc)(tpClass=$clsid))";
                    $org_students = $openldap->findUsers($filter, 'cn');
                    foreach ($org_students as $stu) {
                        if (!in_array($stu['cn'], $students)) {
                            $user_entry = $openldap->getUserEntry($stu['cn']);
                            $openldap->UpdateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
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
