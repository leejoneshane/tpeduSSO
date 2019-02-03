<?php

namespace App\Providers;

use Log;
use Config;
use Illuminate\Support\ServiceProvider;

class LdapServiceProvider extends ServiceProvider
{
    private static $ldap_read = null;
    private static $ldap_write = null;

    public function __construct()
    {
        if (is_null(self::$ldap_read) || is_null(self::$ldap_write))
            $this->connect();
    }

    public function error()
    {
        if (is_null(self::$ldap_read) || is_null(self::$ldap_write)) return;
        return ldap_error(self::$ldap_read).ldap_error(self::$ldap_write);
    }

    public function connect()
    {
		$rhost = Config::get('ldap.rhost');
		if (empty($rhost)) $rhost = Config::get('ldap.host');
		$whost = Config::get('ldap.whost');
		if (empty($whost)) $whost = Config::get('ldap.host');

        if ($ldapconn = @ldap_connect($rhost))
        {
            @ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, intval(Config::get('ldap.version')));
            @ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            self::$ldap_read = $ldapconn;
        }
        else
            Log::error("Connecting LDAP server failed.\n");
		
		if ($ldapconn = @ldap_connect($whost))
		{
			@ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, intval(Config::get('ldap.version')));
			@ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
			self::$ldap_write = $ldapconn;
		}
		else
			Log::error("Connecting LDAP server failed.\n");
	}

    public function administrator() 
    {
		@ldap_bind(self::$ldap_read, Config::get('ldap.rootdn'), Config::get('ldap.rootpwd'));
		@ldap_bind(self::$ldap_write, Config::get('ldap.rootdn'), Config::get('ldap.rootpwd'));
    }
    
    public function authenticate($username, $password)
    {
        if (empty($username) || empty($password)) return false;
    	$account = "uid=$username";
    	$base_dn = Config::get('ldap.authdn');
    	$auth_dn = "$account,$base_dn";
    	return @ldap_bind(self::$ldap_read, $auth_dn, $password);
    }

    public function userLogin($username, $password)
    {
        if (empty($username) || empty($password)) return false;
    	$base_dn = Config::get('ldap.userdn');
    	$user_dn = "$username,$base_dn";
    	return @ldap_bind(self::$ldap_read, $user_dn, $password);
    }

    public function schoolLogin($username, $password)
    {
        if (empty($username) || empty($password)) return false;
    	$base_dn = Config::get('ldap.rdn');
    	$sch_dn = "$username,$base_dn";
    	return @ldap_bind(self::$ldap_read, $sch_dn, $password);
    }

    public function checkIdno($idno)
    {
		if (strlen($idno) == 13) $idno = substr($idno,3);
		if (strlen($idno) != 10) return false;
		$this->administrator();
		$resource = @ldap_search(self::$ldap_read, Config::get('ldap.userdn'), "cn=$idno");
		if ($resource) {
	    	$entry = ldap_first_entry(self::$ldap_read, $resource);
	    	if (!$entry) return false;
	    	$id = ldap_get_values(self::$ldap_read, $entry, 'cn');
	    	return $id[0];
		}
        return false;
    }

    public function checkSchoolAdmin($dc)
    {
		if (empty($dc)) return false;
		$this->administrator();
		$resource = @ldap_search(self::$ldap_read, Config::get('ldap.rdn'), $dc, array("tpAdministrator"));
		if ($resource) {
	    	$entry = ldap_first_entry(self::$ldap_read, $resource);
			if (!$entry) return false;
			return true;
		}
		return false;
    }

    public function checkAccount($username)
    {
        if (empty($username)) return false;
        $filter = "uid=".$username;
		$this->administrator();
		$resource = @ldap_search(self::$ldap_read, Config::get('ldap.authdn'), $filter, array('cn'));
		if ($resource) {
	    	$entry = ldap_first_entry(self::$ldap_read, $resource);
	    	if (!$entry) return false;
	    	$id = ldap_get_values(self::$ldap_read, $entry, 'cn');
	    	return $id[0];
		}
        return false;
    }

    public function checkEmail($email)
    {
        if (empty($email)) return false;
        $filter = "mail=".$email."*";
		$this->administrator();
		$resource = @ldap_search(self::$ldap_read, Config::get('ldap.userdn'), $filter, array('cn'));
		if ($resource) {
	    	$entry = ldap_first_entry(self::$ldap_read, $resource);
	    	if (!$entry) return false;
	    	$id = ldap_get_values(self::$ldap_read, $entry, 'cn');
	    	return $id[0];
		} 
        return false;
    }

    public function checkMobil($mobile)
    {
        if (empty($mobile)) return false;
        $filter = "mobile=".$mobile;
		$this->administrator();
		$resource = ldap_search(self::$ldap_read, Config::get('ldap.userdn'), $filter, array('cn'));
		if ($resource) {
	    	$entry = @ldap_first_entry(self::$ldap_read, $resource);
	    	if (!$entry) return false;
	    	$id = ldap_get_values(self::$ldap_read, $entry, 'cn');
	    	return $id[0];
		} 
        return false;
    }

    public function checkStatus($idno)
    {
        if (empty($idno)) return false;
		$this->administrator();
		$entry = $this->getUserEntry($idno);
		$data = $this->getUserData($entry, 'inetUserStatus');
		if ($data) {
			if ($data['inetUserStatus'] == 'inactive') return 'inactive';
			if ($data['inetUserStatus'] == 'deleted') return 'deleted';
			return 'active';
		}
		return false;
    }

    public function accountAvailable($idno, $account)
    {
        if (empty($idno) || empty($account)) return;

        $filter = "(&(uid=$account)(!(cn=$idno)))";
		$this->administrator();
		$resource = @ldap_search(self::$ldap_read, Config::get('ldap.userdn'), $filter);
		if (ldap_first_entry(self::$ldap_read, $resource)) {
	    	return false;
		} else {
            return true;
		}
    }

    public function emailAvailable($idno, $mailaddr)
    {
        if (empty($idno) || empty($mailaddr)) return;

        $filter = "(&(mail=$mailaddr)(!(cn=$idno)))";
		$this->administrator();
		$resource = @ldap_search(self::$ldap_read, Config::get('ldap.userdn'), $filter);
		if (ldap_first_entry(self::$ldap_read, $resource)) {
	    	return false;
		} else {
            return true;
		}
    }

    public function mobileAvailable($idno, $mobile)
    {
        if (empty($idno) || empty($mobile)) return;

        $filter = "(&(mobile=$mobile)(!(cn=$idno)))";
		$this->administrator();
		$resource = @ldap_search(self::$ldap_read, Config::get('ldap.userdn'), $filter);
		if (ldap_first_entry(self::$ldap_read, $resource)) {
	    	return false;
		} else {
            return true;
		}
    }

    public function getOrgs($filter = '')
    {
		$schools = array();
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		if (empty($filter)) $filter = "objectClass=tpeduSchool";
		$resource = @ldap_search(self::$ldap_read, $base_dn, $filter, ['o', 'st', 'tpUniformNumbers', 'description']);
		$entry = @ldap_first_entry(self::$ldap_read, $resource);
		if ($entry) {
		    do {
	    		$school = new \stdClass();
	    		foreach (['o', 'st', 'tpUniformNumbers', 'description'] as $field) {
					$value = @ldap_get_values(self::$ldap_read, $entry, $field);
					if ($value) $school->$field = $value[0];
	    		}
	    		$schools[] = $school;
		    } while ($entry=ldap_next_entry(self::$ldap_read, $entry));
		}
		return $schools;
    }

    public function getOrgEntry($identifier)
    {
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = "dc=$identifier";
		$resource = @ldap_search(self::$ldap_read, $base_dn, $sch_rdn);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			return $entry;
		}
		return false;
    }
    
    public function getOrgData($entry, $attr = '')
    {
		$fields = array();
		if (is_array($attr)) {
	    	$fields = $attr;
		} elseif ($attr == '') {
	    	$fields[] = 'o';
	    	$fields[] = 'businessCategory';
	    	$fields[] = 'st';
	    	$fields[] = 'description';
	    	$fields[] = 'facsimileTelephoneNumber';
	    	$fields[] = 'telephoneNumber';
	    	$fields[] = 'postalCode';
	    	$fields[] = 'street';
	    	$fields[] = 'postOfficeBox';
	    	$fields[] = 'wWWHomePage';
	    	$fields[] = 'tpUniformNumbers';
	    	$fields[] = 'tpSims';
	    	$fields[] = 'tpIpv4';
	    	$fields[] = 'tpIpv6';
	    	$fields[] = 'tpAdministrator';
		} else {
	    	$fields[] = $attr;
		}
	
		$info = array();
        foreach ($fields as $field) {
	    	$value = @ldap_get_values(self::$ldap_read, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$info[$field] = $value[0];
				} else {
		    		unset($value['count']);
		    		$info[$field] = $value;
				}
	    	}
		}
		return $info;
    }
    
    public function getOrgTitle($dc)
    {
		if (empty($dc)) return '';
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = "dc=$dc";
		$sch_dn = "$sch_rdn,$base_dn";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, "objectClass=tpeduSchool", array("description"));
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldap_read, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function getOrgID($dc)
    {
		if (empty($dc)) return '';
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = "dc=$dc";
		$sch_dn = "$sch_rdn,$base_dn";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, "objectClass=tpeduSchool", [ 'tpUniformNumbers' ]);
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldap_read, $entry, "tpUniformNumbers");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function renameOrg($old_dc, $new_dc)
    {
		$this->administrator();
		$dn = "dc=$old_dc,".Config::get('ldap.rdn');
		$rdn = "dc=$new_dc";
		$result = @ldap_rename(self::$ldap_write, $dn, $rdn, null, true);
		if ($result) {
			$users = $this->findUsers("o=$old_dc");
			if ($users) {
				foreach ($users as $user) {
					$this->UpdateData($user, [ 'o' => $new_dc ]); 
				}
			}
		}
		return $result;
    }

    public function getOus($dc, $category = '')
    {
		$ous = array();
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = "dc=$dc";
		$sch_dn = "$sch_rdn,$base_dn";
		$filter = "objectClass=organizationalUnit";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, $filter, ["businessCategory", "ou", "description"]);
		$entry = @ldap_first_entry(self::$ldap_read, $resource);
		if ($entry) {
			do {
	    		$ou = new \stdClass();
	    		$info = $this->getOuData($entry);
	    		if (!empty($category) && $info['businessCategory'] != $category) continue;
				$ou->ou = $info['ou'];
	    		$ou->description = $info['description'];
				if ($info['businessCategory'] == '教學班級') {
					$ou->grade = substr($info['ou'], 0, 1);
					$teacher = $this->findUsers("(&(o=$dc)(tpTutorClass=".$info['ou']."))", 'cn');
					if ($teacher) $ou->teacher = $teacher[0]['cn'];
				}
	    		$ous[] = $ou;
			} while ($entry=ldap_next_entry(self::$ldap_read, $entry));
			return $ous;
		}
		return false;
    }
    
    public function getOuEntry($dc, $ou)
    {
		$this->administrator();
		$sch_dn = "dc=$dc,".Config::get('ldap.rdn');
		$filter = "ou=$ou";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, $filter);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			return $entry;
		}
		return false;
    }
    
    public function getOuData($entry, $attr='')
    {
		$fields = array();
		if (is_array($attr)) {
	    	$fields = $attr;
		} elseif ($attr == '') {
	    	$fields[] = 'ou';
	    	$fields[] = 'businessCategory';
	    	$fields[] = 'description';
		} else {
	    	$fields[] = $attr;
		}
	
		$info = array();
        	foreach ($fields as $field) {
	    	$value = @ldap_get_values(self::$ldap_read, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$info[$field] = $value[0];
				} else {
		    		unset($value['count']);
		    		$info[$field] = $value;
				}
	    	}
		}
		return $info;
    }
    
    public function getOuTitle($dc, $ou)
    {
		if (empty($dc)) return '';
		$this->administrator();
		$sch_dn = "dc=$dc,".Config::get('ldap.rdn');
		$filter = "ou=$ou";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, $filter, array("description"));
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldap_read, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function updateOus($dc, array $ous)
    {
		if (empty($dc) || empty($ous)) return false;
		$this->administrator();
		foreach ($ous as $ou) {
			if (!isset($ou->id) || !isset($ou->name) || !isset($ou->roles)) return false;
			$entry = $this->getOuEntry($dc, $ou->id);
			if ($entry) {
				$this->updateData($entry, array( "description" => $ou->name));
				foreach ($ou->roles as $role => $desc) {
					if (empty($role) || empty($desc)) return false;
					$role_entry = $this->getRoleEntry($dc, $ou->id, $role);
					if ($role_entry) {
						$this->updateData($role_entry, array( "description" => $desc));
					} else {
						$dn = "cn=$role,ou=$ou->id,dc=$dc,".Config::get('ldap.rdn');
						$this->createEntry(array( "dn" => $dn, "ou" => $ou->id, "cn" => $role, "description" => $desc));
					}
				}
			} else {
				$dn = "ou=$ou->id,dc=$dc,".Config::get('ldap.rdn');
				$this->createEntry(array( "dn" => $dn, "ou" => $ou->id, "businessCategory" => "行政部門", "description" => $ou->name));
				foreach ($ou->roles as $role => $desc) {
					if (empty($role) || empty($desc)) return false;
					$dn = "cn=$role,ou=$ou->id,dc=$dc,".Config::get('ldap.rdn');
					$this->createEntry(array( "dn" => $dn, "ou" => $ou->id, "cn" => $role, "description" => $desc));
				}
			}
		}
		return true;
    }

	public function updateClasses($dc, array $classes)
    {
		if (empty($dc) || empty($classes)) return false;
		$this->administrator();
		foreach ($classes as $class => $title) {
			if (empty($class) || empty($title)) return false;
			$entry = $this->getOuEntry($dc, $class);
			if ($entry) {
				$this->updateData($entry, array( "description" => $title));
			} else {
				$dn = "ou=$class,dc=$dc,".Config::get('ldap.rdn');
				$this->createEntry(array( "dn" => $dn, "ou" => $class, "businessCategory" => "教學班級", "description" => $title));
			}
		}
		return true;
    }

    public function getSubjects($dc)
    {
		$subjs = array();
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = "dc=$dc";
		$sch_dn = "$sch_rdn,$base_dn";
		$filter = "objectClass=tpeduSubject";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, $filter, ["tpSubject", "tpSubjectDomain", "description"]);
		$entry = @ldap_first_entry(self::$ldap_read, $resource);
		if ($entry) {
			do {
	    		$subjs[] = $this->getSubjectData($entry);
			} while ($entry=ldap_next_entry(self::$ldap_read, $entry));
		}
		return $subjs;
    }
    
    public function getSubjectEntry($dc, $subj)
    {
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = "dc=$dc";
		$sch_dn = "$sch_rdn,$base_dn";
		$filter = "tpSubject=$subj";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, $filter);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			return $entry;
		}
		return false;
    }
    
    public function getSubjectData($entry, $attr='')
    {
		$fields = array();
		if (is_array($attr)) {
	    	$fields = $attr;
		} elseif ($attr == '') {
	    	$fields[] = 'tpSubject';
	    	$fields[] = 'tpSubjectDomain';
	    	$fields[] = 'description';
		} else {
	    	$fields[] = $attr;
		}
	
		$info = array();
        foreach ($fields as $field) {
	    	$value = @ldap_get_values(self::$ldap_read, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$info[$field] = $value[0];
				} else {
		    		unset($value['count']);
		    		$info[$field] = $value;
				}
	    	}
		}
		return $info;
    }
    
	public function getSubjectTitle($dc, $subj)
    {
		if (empty($dc) || empty($subj)) return '';
		$this->administrator();
		$sch_dn = "dc=$dc,".Config::get('ldap.rdn');
		$filter = "tpSubject=$subj";
		$resource = @ldap_search(self::$ldap_read, $sch_dn, $filter, array("description"));
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldap_read, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function updateSubjects($dc, array $subjects)
    {
		if (empty($dc) || empty($subjects)) return false;
		$this->administrator();
		foreach ($subjects as $subj) {
			if (!isset($subj->id) || !isset($subj->domain) || !isset($subj->title)) return false;
			$entry = $this->getSubjectEntry($dc, $subj->id);
			if ($entry) {
				$this->updateData($entry, array( "tpSubjectDomain" => $subj->domain, "description" => $subj->title));
			} else {
				$dn = "tpSubject=$subj->id,dc=$dc,".Config::get('ldap.rdn');
				$this->createEntry(array( "dn" => $dn, "tpSubject" => $subj->id, "tpSubjectDomain" => $subj->domain, "description" => $subj->title));
			}
		}
		return true;
    }

    public function allRoles($dc)
    {
		$roles = array();
		$this->administrator();
		$ous = $this->getOus($dc, '行政部門');
		if (!empty($ous)) {
			foreach ($ous as $ou) {
				$ou_id = $ou->ou;
				$uname = $ou->description;
				$info = $this->getRoles($dc, $ou_id);
				foreach ($info as $role_obj) {
					$role = new \stdClass();
					$role->cn = "$ou_id,".$role_obj->cn;
					$role->description = $uname.$role_obj->description;
					$roles[] = $role;
				}
			}
		}
		return $roles;
    }
    
    public function getRoles($dc, $ou)
    {
		$roles = array();
		$this->administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = "dc=$dc";
		$sch_dn = "$sch_rdn,$base_dn";
		$ou_dn = "ou=$ou,$sch_dn";
		$filter = "objectClass=organizationalRole";
		$resource = @ldap_search(self::$ldap_read, $ou_dn, $filter, ["cn", "description"]);
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				do {
		    		$role = new \stdClass();
		    		$info = $this->getRoleData($entry);
		    		$role->cn = $info['cn'];
		    		$role->description = $info['description'];
	    			$roles[] = $role;
				} while ($entry=ldap_next_entry(self::$ldap_read, $entry));
			}
		}
		return $roles;
    }
    
    public function getRoleEntry($dc, $ou, $role_id)
    {
		$this->administrator();
		$ou_dn = "ou=$ou,dc=$dc,".Config::get('ldap.rdn');
		$filter = "cn=$role_id";
		$resource = @ldap_search(self::$ldap_read, $ou_dn, $filter);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			return $entry;
		}
		return false;
    }
    
    public function getRoleData($entry, $attr='')
    {
		$fields = array();
		if (is_array($attr)) {
	    	$fields = $attr;
		} elseif ($attr == '') {
	    	$fields[] = 'ou';
	    	$fields[] = 'cn';
	    	$fields[] = 'description';
		} else {
	    	$fields[] = $attr;
		}
	
		$info = array();
        foreach ($fields as $field) {
	    	$value = @ldap_get_values(self::$ldap_read, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$info[$field] = $value[0];
				} else {
		    		unset($value['count']);
		    		$info[$field] = $value;
				}
	    	}
		}
		return $info;
    }
    
    public function getRoleTitle($dc, $ou, $role)
    {
		if (empty($dc)) return '';
		$this->administrator();
		$ou_dn = "ou=$ou,dc=$dc,".Config::get('ldap.rdn');
		$filter = "cn=$role";
		$resource = @ldap_search(self::$ldap_read, $ou_dn, $filter, array("description"));
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldap_read, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function findUsers($filter, $attr = '')
    {
		$userinfo = array();
		$this->administrator();
		$base_dn = Config::get('ldap.userdn');
		$resource = @ldap_search(self::$ldap_read, $base_dn, $filter, array("*","entryUUID","modifyTimestamp"));
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				do {
	    			$userinfo[] = $this->getUserData($entry, $attr);
				} while ($entry=ldap_next_entry(self::$ldap_read, $entry));
			}
			return $userinfo;
		}
		return false;
    }

    public function getUserEntry($identifier)
    {
		$this->administrator();
		$base_dn = Config::get('ldap.userdn');
		if (strlen($identifier) == 10) { //idno
	    	$filter = "cn=$identifier";
		} else { //uuid
	    	$filter = "entryUUID=$identifier";
		}
		$resource = @ldap_search(self::$ldap_read, $base_dn, $filter, array("*","entryUUID","modifyTimestamp"));
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			return $entry;
		}
		return false;
    }
    
    public function getUserData($entry, $attr = '')
    {
		$fields = array();
		if ($attr == '') {
			$fields[] = 'entryUUID';
			$fields[] = 'modifyTimestamp';
	    	$fields[] = 'cn';
	    	$fields[] = 'o';
	    	$fields[] = 'ou';
	    	$fields[] = 'uid';
	    	$fields[] = 'info';
	    	$fields[] = 'title';
	    	$fields[] = 'gender';
	    	$fields[] = 'birthDate';
	    	$fields[] = 'sn';
	    	$fields[] = 'givenName';
	    	$fields[] = 'displayName';
	    	$fields[] = 'mail';
	    	$fields[] = 'mobile';
	    	$fields[] = 'facsimileTelephoneNumber';
	    	$fields[] = 'telephoneNumber';
	    	$fields[] = 'homePhone';
	    	$fields[] = 'registeredAddress';
	    	$fields[] = 'homePostalAddress';
	    	$fields[] = 'wWWHomePage';
	    	$fields[] = 'employeeType';
	    	$fields[] = 'employeeNumber';
	    	$fields[] = 'tpClass';
	    	$fields[] = 'tpClassTitle';
	    	$fields[] = 'tpSeat';
	    	$fields[] = 'tpTeachClass';
	    	$fields[] = 'tpTutorClass';
	    	$fields[] = 'tpCharacter';
	    	$fields[] = 'tpAdminSchools';
	    	$fields[] = 'inetUserStatus';
		} elseif (is_array($attr)) {
	    	$fields = $attr;
		} else {
	    	$fields[] = $attr;
		}
		if (in_array('uid',$fields)) {
			$fields = array_values(array_unique($fields + ['mail', 'mobile']));
		}
		if (in_array('ou',$fields) || in_array('tpClass',$fields) || in_array('tpTeachClass',$fields)) {
			$fields = array_values(array_unique($fields + ['o']));
		}
		if (in_array('tpAdminSchools',$fields)) {
			$fields = array_values(array_unique($fields + ['o', 'cn']));
		}
		if (in_array('title',$fields)) {
			$fields = array_values(array_unique($fields + ['o', 'ou']));
		}
	
		$userinfo = array();
        foreach ($fields as $field) {
	    	$value = @ldap_get_values(self::$ldap_read, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$userinfo[$field] = $value[0];
				} else {
		    		unset($value['count']);
		    		$userinfo[$field] = $value;
				}
	    	}
		}
		if (!empty($userinfo['tpClass'])) {
			$classname = $this->getOuTitle($userinfo['o'], $userinfo['tpClass']);
			if (!empty($classname) && (!isset($userinfo['tpClassTitle']) || $userinfo['tpClassTitle'] != $classname)) {
				$this->updateData($entry, [ "tpClassTitle" => $classname ]);
			}
		}
		if (in_array('inetUserStatus', $fields) && !isset($userinfo['inetUserStatus'])) {
			$userinfo['inetUserStatus'] = 'active';
			$this->addData($entry, [ "inetUserStatus" => "active" ]);
		}
		if (isset($userinfo['inetUserStatus']) && $userinfo['inetUserStatus'] == 'Active') {
			$userinfo['inetUserStatus'] = 'active';
			$this->updateData($entry, [ "inetUserStatus" => "active" ]);
		}
		$userinfo['email_login'] = false;
		$userinfo['mobile_login'] = false;
		if (isset($userinfo['uid']) && is_array($userinfo['uid'])) {
	    	if (isset($userinfo['mail'])) {
	    		if (is_array($userinfo['mail'])) {
					foreach ($userinfo['mail'] as $mail) {
						if (in_array($mail, $userinfo['uid'])) $userinfo['email_login'] = true;
					}
	    		} else {
	    			if (in_array($userinfo['mail'], $userinfo['uid'])) $userinfo['email_login'] = true;
	    		}
	    	}
	    	if (isset($userinfo['mobile'])) {
	    		if (is_array($userinfo['mobile'])) {
					foreach ($userinfo['mobile'] as $mobile) {
						if (in_array($mobile, $userinfo['uid'])) $userinfo['mobile_login'] = true;
					}
				} else {
	    			if (in_array($userinfo['mobile'], $userinfo['uid'])) $userinfo['mobile_login'] = true;
	    		}
	    	}
		}
		$userinfo['adminSchools'] = false;
		$as = array();
		if (isset($userinfo['tpAdminSchools'])) {
			if (is_array($userinfo['tpAdminSchools'])) {
				$as = $userinfo['tpAdminSchools'];
			} else {
				$as[] = $userinfo['tpAdminSchools'];
			}
		}
		if (isset($userinfo['o']) && !is_array($userinfo['o']) && !in_array($userinfo['o'], $as)) {
			$as[] = $userinfo['o'];
		}
		if (isset($userinfo['cn'])) {
			foreach ($as as $o) {
				$sch_entry = $this->getOrgEntry($o);
				$admins = $this->getOrgData($sch_entry, "tpAdministrator");
				if (isset($admins['tpAdministrator'])) {
					if (is_array($admins['tpAdministrator'])) {
						if (in_array($userinfo['cn'], $admins['tpAdministrator'])) $userinfo['adminSchools'][] = $o;
					} else {
						if ($userinfo['cn'] == $admins['tpAdministrator']) $userinfo['adminSchools'][] = $o;
					}
				}
			}
		}
		$orgs = array();
		if (isset($userinfo['o'])) {
			if (is_array($userinfo['o'])) {
				$orgs = $userinfo['o'];
			} else {
				$orgs[] = $userinfo['o'];
			}
			foreach ($orgs as $o) {
				$userinfo['school'][$o] = $this->getOrgTitle($o);
			}
		}
		$units = array();
		$ous = array();
		if (isset($userinfo['ou'])) {
			if (is_array($userinfo['ou'])) {
				$units = $userinfo['ou'];
			} else {
				$units[] = $userinfo['ou'];
			}
			foreach ($units as $ou_pair) {
				$a = explode(',' , $ou_pair);
				if (count($a) == 2) {
					$o = $a[0];
					$ou = $a[1];
				} else {
					$o = $orgs[0];
					$ou = $a[0];
				}
				$ous[] = $ou;
				$userinfo['department'][$o][$ou_pair] = $this->getOuTitle($o, $ou);
			}
			if (!is_array($userinfo['ou'])) $userinfo['ou'] = $orgs[0].",".$userinfo['ou'];
			$roles = array();
			if (isset($userinfo['title'])) {
				if (is_array($userinfo['title'])) {
					$roles = $userinfo['title'];
				} else {
					$roles[] = $userinfo['title'];
				}
				foreach ($roles as $role_pair) {
					$a = explode(',' , $role_pair);
					if (count($a) == 3 ) {
						$o = $a[0];
						$ou = $a[1];
						$role = $a[2];
					} else {
						$o = $orgs[0];
						$ou = $ous[0];
						$role = $a[0];
					}
					$titles[] = "$o,$ou,$role";
					$userinfo['titleName'][$o][$role_pair] = $this->getRoleTitle($o, $ou, $role);
				}
				$userinfo['title'] = $titles;
			}
		}
		if (isset($userinfo['tpTeachClass'])) {
			if (is_array($userinfo['tpTeachClass'])) {
				$classes = $userinfo['tpTeachClass'];
			} else {
				$classes[] = $userinfo['tpTeachClass'];
			}
			foreach ($classes as $class_pair) {
				$a = explode(',' , $class_pair);
				if (count($a) == 3) {
					$o = $a[0];
					$class = $a[1];
					$subject = '';
					if (isset($a[2])) $subject = $a[2];
				} else {
					$o = $orgs[0];
					$class = $a[0];
					$subject = '';
					if (isset($a[1])) $subject = $a[1];
				}
				$tclass[] = "$o,$class,$subject";
				$userinfo['teachClass'][$o][$class_pair] = $this->getOuTitle($o, $class).$this->getSubjectTitle($o, $subject);
			}
			$userinfo['tpTeachClass'] = $tclass;
		}
		return $userinfo;
    }

	public function getUserName($identifier)
    {
		$entry = $this->getUserEntry($identifier);
		$name = $this->getUserData($entry, 'displayName');
		return $name['displayName'];
    }

	public function getUserAccounts($identifier)
    {
		$this->administrator();
		$entry = $this->getUserEntry($identifier);
		$data = $this->getUserData($entry, ['uid', 'mail', 'mobile']);
		$accounts = array();
		if (!isset($data['uid'])) return $accounts;
		if (is_array($data['uid'])) {
			$accounts = $data['uid'];
		} else {
			$accounts[] = $data['uid'];
		}
		for ($i=0;$i<count($accounts);$i++) {
			if (is_numeric($accounts[$i])) {
				unset($accounts[$i]);
				continue;
			}
			if (strpos($accounts[$i], '@')) {
				unset($accounts[$i]);
				continue;
			}
		}
		return array_values($accounts);
    }

    public function renameUser($old_idno, $new_idno)
    {
		$this->administrator();
		$dn = "cn=$old_idno,".Config::get('ldap.userdn');
		$rdn = "cn=$new_idno";
		$entry = $this->getUserEntry($old_idno);
		$new_pwd = $this->make_ssha_password(substr($new_idno, -6));
		$this->updateData($entry, ["userPassword" => $new_pwd]);
		$accounts = @ldap_get_values(self::$ldap_read, $entry, "uid");
		for($i=0;$i<$accounts['count'];$i++) {
			$account_entry = $this->getAccountEntry($accounts[$i]);
			$this->updateData($account_entry, array( "cn" => $new_idno ));
		}
		$result = @ldap_rename(self::$ldap_write, $dn, $rdn, null, true);
		return $result;
    }

    public function addData($entry, array $fields)
    {
		$this->administrator();
		$fields = array_filter($fields);
		$dn = @ldap_get_dn(self::$ldap_read, $entry);
		$value = @ldap_mod_add(self::$ldap_write, $dn, $fields);
		if (!$value && Config::get('ldap.debug')) Log::debug("Data can't add into $dn:\n".$this->error()."\n".print_r($fields, true)."\n");
		return $value;
    }

    public function updateData($entry, array $fields)
    {
		$this->administrator();
		$dn = @ldap_get_dn(self::$ldap_read, $entry);
		$value = @ldap_mod_replace(self::$ldap_write, $dn, $fields);
		if (!$value && Config::get('ldap.debug')) Log::debug("Data can't update to $dn:\n".$this->error()."\n".print_r($fields, true)."\n");
		return $value;
    }

    public function deleteData($entry, array $fields)
    {
		$this->administrator();
		$dn = @ldap_get_dn(self::$ldap_read, $entry);
		$attrs = @ldap_get_attributes(self::$ldap_read, $entry);
		foreach ($fields as $k => $field) {
			if (!in_array($field, $attrs)) unset($fields[$k]);
		}
		$fields = array_values($fields);
		$value = @ldap_mod_del(self::$ldap_write, $dn, $fields);
		if (!$value && Config::get('ldap.debug')) Log::debug("Data can't remove from $dn:\n".$this->error()."\n".print_r($fields, true)."\n");
		return $value;
    }

    public function createEntry(array $info)
    {
		$this->administrator();
		$dn = $info['dn'];
		unset($info['dn']);
		$info = array_filter($info);
		$value = @ldap_add(self::$ldap_write, $dn, $info);
		if (!$value && Config::get('ldap.debug')) Log::debug("Entry can't create for $dn:\n".$this->error()."\n".print_r($info, true)."\n");
		return $value;
    }

    public function deleteEntry($entry)
    {
		$this->administrator();
		$dn = @ldap_get_dn(self::$ldap_read, $entry);
		$value = @ldap_delete(self::$ldap_write, $dn);
		if (!$value && Config::get('ldap.debug')) Log::debug("Entry can't delete for $dn:\n".$this->error());
		return $value;
    }

    public function findAccounts($filter, $attr = '')
    {
		$accountinfo = array();
		$this->administrator();
		$base_dn = Config::get('ldap.authdn');
		$resource = @ldap_search(self::$ldap_read, $base_dn, $filter);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			if ($entry) {
				do {
	    			$accountinfo[] = $this->getAccountData($entry, $attr);
				} while ($entry=ldap_next_entry(self::$ldap_read, $entry));
			}
			return $accountinfo;
		}
		return false;
    }

    public function getAccountEntry($identifier)
    {
		$this->administrator();
		$base_dn = Config::get('ldap.authdn');
		$auth_rdn = "uid=$identifier";
		$resource = @ldap_search(self::$ldap_read, $base_dn, $auth_rdn);
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldap_read, $resource);
			return $entry;
		}
		return false;
    }

    public function getAccountData($entry, $attr = '')
    {
		$fields = array();
		if ($attr == '') {
	    	$fields[] = 'cn';
	    	$fields[] = 'uid';
	    	$fields[] = 'userPassword';
	    	$fields[] = 'description';
		} elseif (is_array($attr)) {
	    	$fields = $attr;
		} else {
	    	$fields[] = $attr;
		}

		$info = array();
        foreach ($fields as $field) {
	    	$value = @ldap_get_values(self::$ldap_read, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$info[$field] = $value[0];
				} else {
		    		unset($value['count']);
		    		$info[$field] = $value;
				}
	    	}
		}
		return $info;
	}

    public function updateAccounts($entry, $accounts)
    {
		if (!$entry) return false;
		$this->administrator();
		$data = $this->getUserData($entry, ['cn', 'uid']);
		if (!isset($data['uid']) || empty($data['uid'])) {
			if (!empty($accounts))
				foreach ($accounts as $account) {
					$this->addAccount($entry, $account, '自建帳號');
				}
		} else {
			$uids = array();
			if (is_array($data['uid'])) {
				$uids = $data['uid'];
			} else {
				$uids[] = $data['uid'];
			}
			foreach ($uids as $uid) {
				if (!in_array($uid, $accounts)) $this->deleteAccount($entry, $uid);
			}
			if (!empty($accounts)) {
				foreach ($accounts as $account) {
					if (!in_array($account, $uids)) $this->addAccount($entry, $account, '自建帳號');
				}
			}
		}

		$idno = $data['cn'];
		$acc_data = $this->findAccounts("cn=$idno", "uid");
		if (!empty($acc_data)) {
			foreach ($acc_data as $acc) {
				if (!in_array($acc['uid'], $accounts))  $this->deleteAccount($entry, $acc['uid']);
			}
		}
		return true;
    }

    public function resetPassword($entry, $pwd)
    {
		if (!$entry) return;
		$this->administrator();
		$ssha = $this->make_ssha_password($pwd);
		$new_passwd = array( 'userPassword' => $ssha );
		$data = $this->getUserData($entry, 'uid');
		$accounts = array();
		if (isset($data['uid'])) {
			if (is_array($data['uid'])) {
				$accounts = $data['uid'];
			} else {
				$accounts[] = $data['uid'];
			}
			foreach ($accounts as $account) {
				$acc_entry = $openldap->getAccountEntry($account);
				if ($acc_entry) $openldap->updateData($acc_entry,$new_passwd);
			}
		}
		$this->updateData($entry,$new_passwd);
    }

    public function addAccount($entry, $account, $memo)
    {
		$this->administrator();
		$data = $this->getUserData($entry, ['cn', 'userPassword']);
		$idno = $data['cn'];
		$password = $data['userPassword'];
		$this->addData($entry, array( "uid" => $account));

		$account_info = array();
		$account_info['dn'] = "uid=$account,".Config::get('ldap.authdn');
	    $account_info['objectClass'] = "radiusObjectProfile";
	    $account_info['uid'] = $account;
	    $account_info['cn'] = $idno;
	    $account_info['userPassword'] = $password;
	    $account_info['description'] = $memo;
	    $this->createEntry($account_info);
    }

    public function renameAccount($entry, $old_account, $new_account)
    {
		$this->administrator();
		$dn = "uid=$old_account,".Config::get('ldap.authdn');
		$rdn = "uid=$new_account";
		$data = $this->getUserData($entry, 'uid');
		$uid = $data['uid'];
		$accounts = array();
		if (is_array($uid)) {
			$accounts = $uid;
		} else {
			$accounts[] = $uid;
		}
		for ($i=0;$i<count($accounts);$i++) {
	    	if ($accounts[$i] == $old_account) $accounts[$i] = $new_account;
		}
		$this->updateData($entry, array( "uid" => $accounts));
		$result = @ldap_rename(self::$ldap_write, $dn, $rdn, null, true);
 		return $result;
   }

    public function deleteAccount($entry, $account)
    {
		$this->administrator();
		$this->deleteData($entry, array('uid' => $account));
		$acc_entry = $this->getAccountEntry($account);
		if ($acc_entry) $this->deleteEntry($acc_entry);;
    }

    public function getGroupEntry($grp)
    {
		$this->administrator();
		$base_dn = Config::get('ldap.groupdn');
		$grp_rdn = "cn=$grp";
		$resource = ldap_search(self::$ldap_read, $base_dn, $grp_rdn);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldap_read, $resource);
			return $entry;
		}
		return false;
    }

    public function renameGroup($old_grp, $new_grp)
    {
		$this->administrator();
		$dn = "cn=$old_grp,".Config::get('ldap.groupdn');
		$rdn = "cn=$new_grp";
		$result = @ldap_rename(self::$ldap_write, $dn, $rdn, null, true);
		return $result;
    }

    public function getGroups()
    {
		$this->administrator();
        $filter = "objectClass=groupOfURLs";
        $resource = @ldap_search(self::$ldap_read, Config::get('ldap.groupdn'), $filter);
        if ($resource) {
        	$info = @ldap_get_entries(self::$ldap_read, $resource);
        	$groups = array();
        	for ($i=0;$i<$info['count'];$i++) {
		    	$group = new \stdClass();
	    		$group->cn = $info[$i]['cn'][0];
	    		$group->url = $info[$i]['memberurl'][0];
	    		$groups[] = $group;
        	}
        	return $groups;
        }
        return false;
    }

    public function getMembers($identifier)
    {
		$this->administrator();
		$entry = $this->getGroupEntry($identifier);
		if ($entry) {
	    	$data = @ldap_get_values(self::$ldap_read, $entry, "memberURL");
	    	preg_match("/^ldap:\/\/\/".Config::get('ldap.userdn')."\?(\w+)\?sub\?\(.*\)$/", $data[0], $matchs);
	    	$field = $matchs[1];
			$member = array();
	    	$value = @ldap_get_values(self::$ldap_read, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$member[] = $value[0];
				} else {
		    		unset($value['count']);
		    		$member = $value;
				}
	    	}
			$member['attribute'] = $field;
			return $member;
		}
		return false;
     }

    public function ssha_check($text,$hash)
    {
		$ohash = base64_decode(substr($hash,6));
		$osalt = substr($ohash,20);
        $ohash = substr($ohash,0,20);
        $nhash = pack("H*",sha1($text.$osalt));
        return $ohash == $nhash;
    }

    public function make_ssha_password($password)
    {
		$salt = random_bytes(4);
		$hash = "{SSHA}" . base64_encode(pack("H*", sha1($password . $salt)) . $salt);
		return $hash;
    }
    
    public function make_ssha256_password($password)
    {
        $salt = random_bytes(4);
        $hash = "{SSHA256}" . base64_encode(pack("H*", hash('sha256', $password . $salt)) . $salt);
        return $hash;
    }
    
    public function make_ssha384_password($password)
    {
        $salt = random_bytes(4);
        $hash = "{SSHA384}" . base64_encode(pack("H*", hash('sha384', $password . $salt)) . $salt);
        return $hash;
    }
    
    public function make_ssha512_password($password)
    {
        $salt = random_bytes(4);
        $hash = "{SSHA512}" . base64_encode(pack("H*", hash('sha512', $password . $salt)) . $salt);
        return $hash;
    }
    
    public function make_sha_password($password)
    {
        $hash = "{SHA}" . base64_encode(pack("H*", sha1($password)));
        return $hash;
    }
    
    public function make_sha256_password($password)
    {
		$hash = "{SHA256}" . base64_encode(pack("H*", hash('sha256', $password)));
        return $hash;
    }
    
    public function make_sha384_password($password)
    {
        $hash = "{SHA384}" . base64_encode(pack("H*", hash('sha384', $password)));
        return $hash;
    }
    
    public function make_sha512_password($password)
    {
        $hash = "{SHA512}" . base64_encode(pack("H*", hash('sha512', $password)));
        return $hash;
    }
    
    public function make_smd5_password($password)
    {
        $salt = random_bytes(4);
        $hash = "{SMD5}" . base64_encode(pack("H*", md5($password . $salt)) . $salt);
        return $hash;
    }

    public function make_md5_password($password)
    {
        $hash = "{MD5}" . base64_encode(pack("H*", md5($password)));
        return $hash;
    }
    
    public function make_crypt_password($password, $hash_options)
    {
        $salt_length = 2;
        if ( isset($hash_options['crypt_salt_length']) ) {
            $salt_length = $hash_options['crypt_salt_length'];
        }
        // Generate salt
		$possible = '0123456789'.
		    		'abcdefghijklmnopqrstuvwxyz'.
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
    		    	'./';
		$salt = "";
        while( strlen( $salt ) < $salt_length ) {
	    $salt .= substr( $possible, random_int( 0, strlen( $possible ) - 1 ), 1 );
        }
        if ( isset($hash_options['crypt_salt_prefix']) ) {
    	    $salt = $hash_options['crypt_salt_prefix'] . $salt;
        }
        $hash = '{CRYPT}' . crypt( $password,  $salt);
        return $hash;
    }
}
