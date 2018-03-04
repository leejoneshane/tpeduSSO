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

        if (!empty(Config::get('ldap.groupdn')))
            $this->groupList = $this->getGroupList();
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
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $idno);
	if ($resource) {
	    return substr($idno,3);
	}
        return false;
    }

    public function checkSchoolAdmin($dc)
    {
        if (empty($dc)) return false;
	self::administrator();
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.rdn'), $dc);
	if ($resource) {
	    $entry = ldap_first_entry(self::$ldapConnectId, $resource);
	    if (!$entry) return false;
	    $id = @ldap_get_values(self::$ldapConnectId, $entry, "tpAdministrator");
//	    if ($id) return false;
	    return "0000000000";
	}
        return false;
    }

    public function checkAccount($username)
    {
        if (empty($username)) return false;
        $filter = "uid=".$username;
	self::administrator();
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.authdn'), $filter);
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
        $filter = "mail=".$email;
	self::administrator();
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
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
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
	if ($resource) {
	    $entry = ldap_first_entry(self::$ldapConnectId, $resource);
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
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
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
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
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
	$resource = ldap_search(self::$ldapConnectId, Config::get('ldap.userdn'), $filter);
	if (ldap_first_entry(self::$ldapConnectId, $resource)) {
	    return false;
	} else {
            return true;
	}
    }

    public function getAccountEntry($identifier)
    {
	self::administrator();
	$base_dn = Config::get('ldap.authdn');
	$auth_rdn = Config::get('ldap.authattr')."=".$identifier;
	$resource = ldap_search(self::$ldapConnectId, $base_dn, $auth_rdn);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	return $entry;
    }
    
    public function getOrgs()
    {
	$schools = array();
	self::administrator();
	$base_dn = Config::get('ldap.rdn');
	$filter = "(objectClass=tpeduSchool)";
	$resource = ldap_search(self::$ldapConnectId, $base_dn, $filter);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	do {
	    $school = new \stdClass();
	    foreach (['o', 'tpUniformNumbers', 'description'] as $field) {
		$value = @ldap_get_values(self::$ldapConnectId, $entry, $field);
		if ($value) $school->$field = $value[0];
	    }
	    $schools[] = $school;
	} while ($entry=ldap_next_entry(self::$ldapConnectId, $entry));
	return $schools;
    }

    public function getOrgEntry($identifier)
    {
	self::administrator();
	$base_dn = Config::get('ldap.rdn');
	$sch_rdn = Config::get('ldap.schattr')."=".$identifier;
	$resource = ldap_search(self::$ldapConnectId, $base_dn, $sch_rdn);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	return $entry;
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
	    $fields[] = 'fax';
	    $fields[] = 'telephoneNumber';
	    $fields[] = 'PostalCode';
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
    
    public function getOus($dc, $category)
    {
	$ous = array();
	self::administrator();
	$base_dn = Config::get('ldap.rdn');
	$sch_rdn = Config::get('ldap.schattr')."=".$dc;
	$sch_dn = "$sch_rdn,$base_dn";
	$filter = "objectClass=organizationalUnit";
	$resource = ldap_search(self::$ldapConnectId, $sch_dn, $filter);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	do {
	    $ou = new \stdClass();
	    $info = self::getOuData($entry);
	    if ($info['businessCategory'] != $category) continue;
	    $ou->ou = $info['ou'];
	    $ou->description = $info['description'];
	    $ous[] = $ou;
	} while ($entry=ldap_next_entry(self::$ldapConnectId, $entry));
	return $ous;
    }
    
    public function getOuEntry($dc, $ou)
    {
	self::administrator();
	$base_dn = Config::get('ldap.rdn');
	$sch_rdn = Config::get('ldap.schattr')."=".$dc;
	$sch_dn = "$sch_rdn,$base_dn";
	$filter = "ou=$ou";
	$resource = ldap_search(self::$ldapConnectId, $sch_dn, $filter);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	return $entry;
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
	$resource = ldap_search(self::$ldapConnectId, $sch_dn, $filter);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	$value = @ldap_get_values(self::$ldapConnectId, $entry, "description");
	return $value[0];
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
	$resource = ldap_search(self::$ldapConnectId, $ou_dn, $filter);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	do {
	    $role = new \stdClass();
	    $info = self::getRoleData($entry);
	    $role->cn = $info['cn'];
	    $role->description = $info['description'];
	    $roles[] = $role;
	} while ($entry=ldap_next_entry(self::$ldapConnectId, $entry));
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
	$resource = ldap_search(self::$ldapConnectId, $ou_dn, $filter);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	return $entry;
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
	$resource = ldap_search(self::$ldapConnectId, $ou_dn, $filter);
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	$value = @ldap_get_values(self::$ldapConnectId, $entry, "description");
	return $value[0];
    }
    
    public function findUsers($filter)
    {
	self::administrator();
	$base_dn = Config::get('ldap.userdn');
	$resource = ldap_search(self::$ldapConnectId, $base_dn, $filter, array("*","entryUUID"));
	$entries = ldap_get_entries(self::$ldapConnectId, $resource);
	return $entries;
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
	$resource = ldap_search(self::$ldapConnectId, $base_dn, $filter, array("*","entryUUID"));
	$entry = ldap_first_entry(self::$ldapConnectId, $resource);
	return $entry;
    }
    
    public function getUserData($entry, $attr = '')
    {
	$fields = array();
	if (is_array($attr)) {
	    $fields = $attr;
	} elseif ($attr == '') {
	    $fields[] = 'entryUUID';
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
	    $fields[] = 'fax';
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
	    if (in_array($userinfo['mail'], $userinfo['uid'])) 
		$userinfo['email_login'] = true;
	    if (in_array($userinfo['mobile'], $userinfo['uid']))
		$userinfo['mobile_login'] = true;
	}

	if (isset($userinfo['o'])) {
	    $dc = $userinfo['o'];
	    $entry = $this->getOrgEntry($dc);
	    $value = $this->getOrgData($entry, "description");
	    $userinfo['school'] = $value['description'];
	    if (isset($userinfo['ou'])) {
		$ou = $userinfo['ou'];
		$value = $this->getOuTitle($dc, $ou);
		$userinfo['department'] = $value;
		if (isset($userinfo['title'])) {
		    $role = $userinfo['title'];
		    $value = $this->getRoleTitle($dc, $ou, $role);
		    $userinfo['titleName'] = $value;
		}
	    }
	}
	return $userinfo;
    }

    public function addData($entry, array $fields)
    {
	$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
	$value = @ldap_mod_add(self::$ldapConnectId, $dn, $fields);
	return $value;
    }

    public function updateData($entry, array $fields)
    {
	$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
	$value = @ldap_mod_replace(self::$ldapConnectId, $dn, $fields);
	return $value;
    }

    public function deleteData($entry, array $fields)
    {
	$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
	$value = @ldap_mod_del(self::$ldapConnectId, $dn, $fields);
	return $value;
    }

    public function createEntry($info)
    {
	$dn = $info['dn'];
	unset($info['dn']);
	$value = @ldap_delete(self::$ldapConnectId, $dn, $info);
	return $value;
    }

    public function deleteEntry($entry)
    {
	$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
	$value = @ldap_delete(self::$ldapConnectId, $dn);
	return $value;
    }

    public function updateAccount($entry, $old_account, $new_account, $idno, $memo)
    {
	$acc_entry = self::getAccountEntry($old_account);
	if ($acc_entry) {
	    self::renameAccount($entry, $old_account, $new_account);
	} else {
	    self::addAccount($entry, $new_account, $idno, $memo);
	}
    }

    public function addAccount($entry, $account, $idno, $memo)
    {
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
	$dn = Config::get('ldap.authattr')."=".$old_account.",".Config::get('ldap.authdn');
	$rdn = Config::get('ldap.authattr')."=".$new_account;
	$accounts = @ldap_get_values(self::$ldapConnectId, $entry, "uid");
	for($i=0;$i<$accounts['count'];$i++) {
	    if ($accounts[$i] == $old_account) $accounts[$i] = $new_account;
	}
	unset($accounts['count']);
	@ldap_rename(self::$ldapConnectId, $dn, $rdn, null, true);
	self::updateData($entry, array( "uid" => $accounts));
    }

    public function deleteAccount($entry, $account)
    {
	$dn = @ldap_get_dn(self::$ldapConnectId, $entry);
	@ldap_mod_del(self::$ldapConnectId, $dn, array('uid' => $account));
	$dn = Config::get('ldap.authattr')."=".$account.",".Config::get('ldap.authdn');
	@ldap_delete(self::$ldapConnectId, $dn);
    }

    public function getGroupList()
    {
        $ldapFilter = "(cn=*)";
        $attr = array("cn", "gidNumber");
        $searchId = @ldap_search(self::$ldapConnectId, Config::get('ldap.groupdn'), $ldapFilter, $attr);

        if (!$searchId)
            return false;

        $info = @ldap_get_entries(self::$ldapConnectId, $searchId);
        $groupList = array();
        foreach ($info as $each)
        {
            if (!empty($each["cn"][0]))
                $groupList[] = $each["cn"][0];
        }

        return $groupList;
    }

    public function whichGroup($identifier)
    {
        $gidnumber = strval($this->getUserData($identifier)['gidnumber'][0]);
        return $this->groupList[$gidnumber];
    }

    public function groupIsOK()
    {
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

    public function make_ssha_password($password) {
	$salt = random_bytes(4);
	$hash = "{SSHA}" . base64_encode(pack("H*", sha1($password . $salt)) . $salt);
	return $hash;
    }
    
    public function make_ssha256_password($password) {
        $salt = random_bytes(4);
        $hash = "{SSHA256}" . base64_encode(pack("H*", hash('sha256', $password . $salt)) . $salt);
        return $hash;
    }
    
    public function make_ssha384_password($password) {
        $salt = random_bytes(4);
        $hash = "{SSHA384}" . base64_encode(pack("H*", hash('sha384', $password . $salt)) . $salt);
        return $hash;
    }
    
    public function make_ssha512_password($password) {
        $salt = random_bytes(4);
        $hash = "{SSHA512}" . base64_encode(pack("H*", hash('sha512', $password . $salt)) . $salt);
        return $hash;
    }
    
    public function make_sha_password($password) {
        $hash = "{SHA}" . base64_encode(pack("H*", sha1($password)));
        return $hash;
    }
    
    public function make_sha256_password($password) {
	$hash = "{SHA256}" . base64_encode(pack("H*", hash('sha256', $password)));
        return $hash;
    }
    
    public function make_sha384_password($password) {
        $hash = "{SHA384}" . base64_encode(pack("H*", hash('sha384', $password)));
        return $hash;
    }
    
    public function make_sha512_password($password) {
        $hash = "{SHA512}" . base64_encode(pack("H*", hash('sha512', $password)));
        return $hash;
    }
    
    public function make_smd5_password($password) {
        $salt = random_bytes(4);
        $hash = "{SMD5}" . base64_encode(pack("H*", md5($password . $salt)) . $salt);
        return $hash;
    }

    public function make_md5_password($password) {
        $hash = "{MD5}" . base64_encode(pack("H*", md5($password)));
        return $hash;
    }
    
    public function make_crypt_password($password, $hash_options) {
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
