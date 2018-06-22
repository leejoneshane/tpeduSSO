<?php

namespace App\Providers;

use Log;
use Config;
use Illuminate\Support\ServiceProvider;

class LdapServiceProvider extends ServiceProvider
{

    private $groupList;
    private static $ldapConnectId = null;

    public function __construct()
    {
        if (is_null(self::$ldapConnectId))
            $this->connect();
    }

    public function error()
    {
        if (is_null(self::$ldapConnectId)) return;
        return ldap_error(self::$ldapConnectId);
    }

    public function connect()
    {
        if ($ldapconn = @ldap_connect(Config::get('ldap.host')))
        {
            @ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, intval(Config::get('ldap.version')));
            @ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            self::$ldapConnectId = $ldapconn;
        }
        else
            Log::error("Connecting LDAP server failed.\n");
    }

    public function administrator() 
    {
		@ldap_bind(self::$ldapConnectId, Config::get('ldap.rootdn'), Config::get('ldap.rootpwd'));
    }
    
    public function authenticate($username, $password)
    {
        if (empty($username) || empty($password)) return false;
    	$account = Config::get('ldap.authattr')."=".$username;
    	$base_dn = Config::get('ldap.authdn');
    	$auth_dn = "$account,$base_dn";
    	return @ldap_bind(self::$ldapConnectId, $auth_dn, $password);
    }

    public function userLogin($username, $password)
    {
        if (empty($username) || empty($password)) return false;
    	$base_dn = Config::get('ldap.userdn');
    	$user_dn = "$username,$base_dn";
    	return @ldap_bind(self::$ldapConnectId, $user_dn, $password);
    }

    public function schoolLogin($username, $password)
    {
        if (empty($username) || empty($password)) return false;
    	$base_dn = Config::get('ldap.rdn');
    	$sch_dn = "$username,$base_dn";
    	return @ldap_bind(self::$ldapConnectId, $sch_dn, $password);
    }

    public function checkIdno($idno)
    {
    	if (empty($idno)) return false;
		self::administrator();
		$resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $idno);
		if ($resource) {
	    	return substr($idno,3);
		}
        return false;
    }

    public function checkSchoolAdmin($dc)
    {
		if (empty($dc)) return false;
		self::administrator();
		$resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.rdn'), $dc, array("tpAdministrator"));
		if ($resource) {
	    	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
			if (!$entry) return false;
			return true;
		}
		return false;
    }

    public function checkAccount($username)
    {
        if (empty($username)) return false;
        $filter = "uid=".$username;
		self::administrator();
		$resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.authdn'), $filter, array("cn"));
		if ($resource) {
	    	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	    	if (!$entry) return false;
	    	$id = ldap_get_values(self::$ldapConnectId, $entry, "cn");
	    	return $id[0];
		}
        return false;
    }

    public function checkEmail($email)
    {
        if (empty($email)) return false;
        $filter = "mail=".$email."*";
		self::administrator();
		$resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter, array("cn"));
		if ($resource) {
	    	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	    	if (!$entry) return false;
	    	$id = ldap_get_values(self::$ldapConnectId, $entry, "cn");
	    	return $id[0];
		} 
        return false;
    }

    public function checkMobil($mobile)
    {
        if (empty($mobile)) return false;
        $filter = "mobile=".$mobile;
		self::administrator();
		$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter, array("cn"));
		if ($resource) {
	    	$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
	    	if (!$entry) return false;
	    	$id = ldap_get_values(self::$ldapConnectId, $entry, "cn");
	    	return $id[0];
		} 
        return false;
    }

    public function accountAvailable($idno, $account)
    {
        if (empty($idno) || empty($account)) return;

        $filter = "(&(uid=".$account.")(!(".Config::get('ldap.userattr')."=".$idno.")))";
		self::administrator();
		$resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
		if (ldap_first_entry(self::$ldapConnectId, $resource)) {
	    	return false;
		} else {
            return true;
		}
    }

    public function emailAvailable($idno, $mailaddr)
    {
        if (empty($idno) || empty($mailaddr)) return;

        $filter = "(&(mail=".$mailaddr.")(!(".Config::get('ldap.userattr')."=".$idno.")))";
		self::administrator();
		$resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
		if (ldap_first_entry(self::$ldapConnectId, $resource)) {
	    	return false;
		} else {
            return true;
		}
    }

    public function mobileAvailable($idno, $mobile)
    {
        if (empty($idno) || empty($mobile)) return;

        $filter = "(&(mobile=".$mobile.")(!(".Config::get('ldap.userattr')."=".$idno.")))";
		self::administrator();
		$resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
		if (ldap_first_entry(self::$ldapConnectId, $resource)) {
	    	return false;
		} else {
            return true;
		}
    }

    public function getOrgs($filter = '')
    {
		$schools = array();
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		if (empty($filter)) $filter = "objectClass=tpeduSchool";
		$resource = @ldap_search(self::$ldapConnectId, $base_dn, $filter, ['o', 'st', 'tpUniformNumbers', 'description']);
		$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
		if ($entry) {
		    do {
	    		$school = new \stdClass();
	    		foreach (['o', 'st', 'tpUniformNumbers', 'description'] as $field) {
					$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
					if ($value) $school->$field = $value[0];
	    		}
	    		$schools[] = $school;
		    } while ($entry=ldap_next_entry(self::$ldapConnectId, $entry));
		}
		return $schools;
    }

    public function getOrgEntry($identifier)
    {
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$identifier;
		$resource = @ldap_search(self::$ldapConnectId, $base_dn, $sch_rdn);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldapConnectId, $resource);
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
	    	$fields[] = 'tpIpv4';
	    	$fields[] = 'tpIpv6';
	    	$fields[] = 'tpAdministrator';
		} else {
	    	$fields[] = $attr;
		}
	
		$info = array();
        foreach ($fields as $field) {
    	    if ($field == 'ou') continue;
	    	$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
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
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$dc;
		$sch_dn = "$sch_rdn,$base_dn";
		$resource = @ldap_search(self::$ldapConnectId, $sch_dn, "objectClass=tpeduSchool", array("description"));
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldapConnectId, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function renameOrg($old_dc, $new_dc)
    {
		self::administrator();
		$dn = Config::get('ldap.schattr')."=".$old_dc.",".Config::get('ldap.rdn');
		$rdn = Config::get('ldap.schattr')."=".$new_dc;
		$result = @ldap_rename(self::$ldapConnectId, $dn, $rdn, null, true);
		if ($result) {
			$users = $openldap->findUsers("o=$old_dc");
			if ($users) {
				foreach ($users as $user) {
					$openldap->UpdateData($user, [ 'o' => $new_dc ]);
				}
			}
		}
		return $result;
    }

    public function getOus($dc, $category = '')
    {
		$ous = array();
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$dc;
		$sch_dn = "$sch_rdn,$base_dn";
		$filter = "objectClass=organizationalUnit";
		$resource = @ldap_search(self::$ldapConnectId, $sch_dn, $filter, ["businessCategory", "ou", "description"]);
		$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
		if ($entry) {
			do {
	    		$ou = new \stdClass();
	    		$info = self::getOuData($entry);
	    		if (!empty($category) && $info['businessCategory'] != $category) continue;
				$ou->ou = $info['ou'];
				if ($info['businessCategory'] == '教學班級') $ou->grade = substr($info['ou'], 0, 1);
	    		$ou->description = $info['description'];
	    		$ous[] = $ou;
			} while ($entry=ldap_next_entry(self::$ldapConnectId, $entry));
			return $ous;
		}
		return false;
    }
    
    public function getOuEntry($dc, $ou)
    {
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$dc;
		$sch_dn = "$sch_rdn,$base_dn";
		$filter = "ou=$ou";
		$resource = @ldap_search(self::$ldapConnectId, $sch_dn, $filter);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldapConnectId, $resource);
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
	    	$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
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
		self::administrator();
		$sch_dn = Config::get('ldap.schattr')."=$dc,".Config::get('ldap.rdn');
		$filter = "ou=$ou";
		$resource = @ldap_search(self::$ldapConnectId, $sch_dn, $filter, array("description"));
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldapConnectId, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function getSubjects($dc)
    {
		$subjs = array();
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$dc;
		$sch_dn = "$sch_rdn,$base_dn";
		$filter = "objectClass=tpeduSubject";
		$resource = @ldap_search(self::$ldapConnectId, $sch_dn, $filter, ["tpSubject", "tpSubjectDomain", "description"]);
		$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
		if ($entry) {
			do {
	    		$subj = new \stdClass();
	    		$info = self::getSubjectData($entry);
	    		$subj->subject = $info['tpSubject'];
	    		$subj->domain = $info['tpSubjectDomain'];
	    		$subj->description = $info['description'];
	    		$subjs[] = $subj;
			} while ($entry=ldap_next_entry(self::$ldapConnectId, $entry));
		}
		return $subjs;
    }
    
    public function getSubjectEntry($dc, $subj)
    {
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$dc;
		$sch_dn = "$sch_rdn,$base_dn";
		$filter = "tpSubject=$subj";
		$resource = @ldap_search(self::$ldapConnectId, $sch_dn, $filter);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldapConnectId, $resource);
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
	    	$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
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
		self::administrator();
		$sch_dn = Config::get('ldap.schattr')."=$dc,".Config::get('ldap.rdn');
		$filter = "tpSubject=$subj";
		$resource = @ldap_search(self::$ldapConnectId, $sch_dn, $filter, array("description"));
		if ($resource) {
			$entry = ldap_first_entry(self::$ldapConnectId, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldapConnectId, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function getRoles($dc, $ou)
    {
		$roles = array();
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$dc;
		$sch_dn = "$sch_rdn,$base_dn";
		$ou_dn = "ou=$ou,$sch_dn";
		$filter = "objectClass=organizationalRole";
		$resource = @ldap_search(self::$ldapConnectId, $ou_dn, $filter, ["cn", "description"]);
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
			if ($entry) {
				do {
		    		$role = new \stdClass();
		    		$info = self::getRoleData($entry);
		    		$role->cn = $info['cn'];
		    		$role->description = $info['description'];
	    			$roles[] = $role;
				} while ($entry=ldap_next_entry(self::$ldapConnectId, $entry));
			}
		}
		return $roles;
    }
    
    public function getRoleEntry($dc, $ou, $role_id)
    {
		self::administrator();
		$base_dn = Config::get('ldap.rdn');
		$sch_rdn = Config::get('ldap.schattr')."=".$dc;
		$sch_dn = "$sch_rdn,$base_dn";
		$ou_rdn = "ou=$ou";
		$ou_dn = "$ou_rdn,$sch_dn";
		$filter = "cn=$role_id";
		$resource = @ldap_search(self::$ldapConnectId, $ou_dn, $filter);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldapConnectId, $resource);
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
	    	$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
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
		self::administrator();
		$ou_dn = "ou=$ou,".Config::get('ldap.schattr')."=$dc,".Config::get('ldap.rdn');
		$filter = "cn=$role";
		$resource = @ldap_search(self::$ldapConnectId, $ou_dn, $filter, array("description"));
		if ($resource) {
			$entry = @ldap_first_entry(self::$ldapConnectId, $resource);
			if ($entry) {
				$value = @ldap_get_values(self::$ldapConnectId, $entry, "description");
				if ($value) return $value[0];
			}
		}
		return '';
    }
    
    public function findUsers($filter, $attr = null)
    {
		$fields = array();
		if (is_null($attr))
			$fields[] = 'entryUUID';
		else
			if (is_array($attr))
				$fields = $attr;
			else
				$fields[] = $attr;
		
		self::administrator();
		$base_dn = Config::get('ldap.userdn');
		$resource = @ldap_search(self::$ldapConnectId, $base_dn, $filter, $fields);
		if ($resource) {
			$entries = ldap_get_entries(self::$ldapConnectId, $resource);
			return $entries;
		}
		return false;
    }

    public function getUserEntry($identifier)
    {
		self::administrator();
		$base_dn = Config::get('ldap.userdn');
		if (strlen($identifier) == 10) { //idno
	    	$filter = Config::get('ldap.userattr')."=".$identifier;
		} else { //uuid
	    	$filter = "entryUUID=".$identifier;
		}
		$resource = @ldap_search(self::$ldapConnectId, $base_dn, $filter, array("*","entryUUID"));
		if ($resource) {
			$entry = ldap_first_entry(self::$ldapConnectId, $resource);
			return $entry;
		}
		return false;
    }
    
    public function getUserData($entry, $attr = '')
    {
		$fields = array();
		if (is_array($attr)) {
	    	$fields = $attr;
		} elseif ($attr == '') {
	    	$fields[] = 'entryUUID';
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
	    	$fields[] = 'tpCharacter';
	    	$fields[] = 'inetUserStatus';
		} elseif ($attr == 'uid')  {
	    	$fields[] = 'uid';
	    	$fields[] = 'mail';
	    	$fields[] = 'mobile';
		} elseif ($attr == 'title')  {
	    	$fields[] = 'o';
	    	$fields[] = 'ou';
	    	$fields[] = 'title';
		} elseif ($attr == 'ou')  {
	    	$fields[] = 'o';
	    	$fields[] = 'ou';
		} else {
	    	$fields[] = $attr;
		}
	
		$userinfo = array();
        foreach ($fields as $field) {
	    	$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
	    	if ($value) {
				if ($value['count'] == 1) {
		    		$userinfo[$field] = $value[0];
				} else {
		    		unset($value['count']);
		    		$userinfo[$field] = $value;
				}
	    	}
		}
		$userinfo['email_login'] = false;
		$userinfo['mobile_login'] = false;
		if (isset($userinfo['uid']) && is_array($userinfo['uid'])) {
	    	if (isset($userinfo['mail'])) {
	    		if (is_array($userinfo['mail'])) {
	    			if (in_array($userinfo['mail'][0], $userinfo['uid'])) $userinfo['email_login'] = true;
	    		} else {
	    			if (in_array($userinfo['mail'], $userinfo['uid'])) $userinfo['email_login'] = true;
	    		}
	    	}
	    	if (isset($userinfo['mobile'])) {
	    		if (is_array($userinfo['mobile'])) {
	    			if (in_array($userinfo['mobile'][0], $userinfo['uid'])) $userinfo['mobile_login'] = true;
	    		} else {
	    			if (in_array($userinfo['mobile'], $userinfo['uid'])) $userinfo['mobile_login'] = true;
	    		}
	    	}
		}

		if (isset($userinfo['o'])) {
	    	$dc = $userinfo['o'];
	    	$userinfo['school'] = $this->getOrgTitle($dc);
	    	if (isset($userinfo['ou'])) {
				$ou = $userinfo['ou'];
				$userinfo['department'] = $this->getOuTitle($dc, $ou);
				if (isset($userinfo['title'])) {
		    		$role = $userinfo['title'];
		    		$userinfo['titleName'] = $this->getRoleTitle($dc, $ou, $role);
				}
	    	}
		}
		return $userinfo;
    }

    public function renameUser($old_idno, $new_idno)
    {
		self::administrator();
		$dn = Config::get('ldap.userattr')."=".$old_idno.",".Config::get('ldap.userdn');
		$rdn = Config::get('ldap.userattr')."=".$new_idno;
		$entry = self::getUserEntry($old_idno);
		$accounts = @ldap_get_values(self::$ldapConnectId, $entry, "uid");
		for($i=0;$i<$accounts['count'];$i++) {
			$account_entry = self::getAccountEntry($accounts[$i]);
			self::updateData($account_entry, array( "cn" => $new_idno ));
		}
		$result = @ldap_rename(self::$ldapConnectId, $dn, $rdn, null, true);
		return $result;
    }

    public function addData($entry, array $fields)
    {
		$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
		$value = @ldap_mod_add(self::$ldapConnectId, $dn, $fields);
		if (!$value) Log::error("Data can't add into openldap:\n".print_r($fields, true)."\n");
		return $value;
    }

    public function updateData($entry, array $fields)
    {
		$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
		$value = @ldap_mod_replace(self::$ldapConnectId, $dn, $fields);
		if (!$value) Log::error("Data can't update to openldap:\n".print_r($fields, true)."\n");
		return $value;
    }

    public function deleteData($entry, array $fields)
    {
		$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
		$value = @ldap_mod_del(self::$ldapConnectId, $dn, $fields);
		if (!$value) Log::error("Data can't remove from openldap:\n".print_r($fields, true)."\n");
		return $value;
    }

    public function createEntry(array $info)
    {
		self::administrator();
		$dn = $info['dn'];
		unset($info['dn']);
		$value = @ldap_add(self::$ldapConnectId, $dn, $info);
		return $value;
    }

    public function deleteEntry($entry)
    {
		self::administrator();
		$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
		$value = @ldap_delete(self::$ldapConnectId, $dn);
		return $value;
    }

    public function getAccountEntry($identifier)
    {
		self::administrator();
		$base_dn = Config::get('ldap.authdn');
		$auth_rdn = Config::get('ldap.authattr')."=".$identifier;
		$resource = @ldap_search(self::$ldapConnectId, $base_dn, $auth_rdn);
		if ($resource) {
			return @ldap_first_entry(self::$ldapConnectId, $resource);
		}
		return false;
    }
    
    public function updateAccount($entry, $old_account, $new_account, $idno, $memo)
    {
		self::administrator();
		$acc_entry = self::getAccountEntry($old_account);
		if ($acc_entry) {
	    	self::renameAccount($entry, $old_account, $new_account);
		} else {
	    	self::addAccount($entry, $new_account, $idno, $memo);
		}
    }

    public function addAccount($entry, $account, $idno, $memo)
    {
		self::administrator();
		$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
		@ldap_mod_add(self::$ldapConnectId, $dn, array( "uid" => $account));
		$acc_entry = self::getAccountEntry($account);
		if (!$acc_entry) {
	    	$dn = Config::get('ldap.authattr')."=".$account.",".Config::get('ldap.authdn');
	    	$account_info = array();
	    	$account_info['objectClass'] = "radiusObjectProfile";
	    	$account_info['uid'] = $account;
	    	$account_info['cn'] = $idno;
	    	$pwd = @ldap_get_values(self::$ldapConnectId, $entry, "userPassword");
	    	$account_info['userPassword'] = $pwd[0];
	    	$account_info['description'] = $memo;
	    	@ldap_add(self::$ldapConnectId, $dn, $account_info);
		}
    }

    public function renameAccount($entry, $old_account, $new_account)
    {
		self::administrator();
		$dn = Config::get('ldap.authattr')."=".$old_account.",".Config::get('ldap.authdn');
		$rdn = Config::get('ldap.authattr')."=".$new_account;
		$accounts = @ldap_get_values(self::$ldapConnectId, $entry, "uid");
		for($i=0;$i<$accounts['count'];$i++) {
	    	if ($accounts[$i] == $old_account) $accounts[$i] = $new_account;
		}
		unset($accounts['count']);
		self::updateData($entry, array( "uid" => $accounts));

		$result = @ldap_rename(self::$ldapConnectId, $dn, $rdn, null, true);
 		return $result;
   }

    public function deleteAccount($entry, $account)
    {
		self::administrator();
		$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
		@ldap_mod_del(self::$ldapConnectId, $dn, array('uid' => $account));
		$dn = Config::get('ldap.authattr')."=".$account.",".Config::get('ldap.authdn');
		@ldap_delete(self::$ldapConnectId, $dn);
    }

    public function getGroupEntry($cn)
    {
		self::administrator();
		$base_dn = Config::get('ldap.groupdn');
		$grp_rdn = Config::get('ldap.groupattr')."=".$cn;
		$resource = ldap_search(self::$ldapConnectId, $base_dn, $grp_rdn);
		if ($resource) {
			$entry = ldap_first_entry(self::$ldapConnectId, $resource);
			return $entry;
		}
		return false;
    }

    public function renameGroup($old_cn, $new_cn)
    {
		self::administrator();
		$dn = Config::get('ldap.groupattr')."=".$old_cn.",".Config::get('ldap.groupdn');
		$rdn = Config::get('ldap.groupattr')."=".$new_cn;
		$result = @ldap_rename(self::$ldapConnectId, $dn, $rdn, null, true);
		return $result;
    }

    public function getGroups()
    {
		self::administrator();
        $filter = "objectClass=groupOfURLs";
        $resource = @ldap_search(self::$ldapConnectId, Config::get('ldap.groupdn'), $filter);
        if ($resource) {
        	$info = @ldap_get_entries(self::$ldapConnectId, $resource);
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
		self::administrator();
		$entry = self::getGroupEntry($identifier);
		if ($entry) {
	    	$data = @ldap_get_values(self::$ldapConnectId, $entry, "memberURL");
	    	preg_match("/^ldap:\/\/\/".Config::get('ldap.userdn')."\?(\w+)\?sub\?\(.*\)$/", $data[0], $matchs);
	    	$field = $matchs[1];
			$member = array();
	    	$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
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
