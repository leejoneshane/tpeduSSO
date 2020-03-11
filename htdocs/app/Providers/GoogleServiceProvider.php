<?php
namespace App\Providers;

use Log;
use Config;
use App\User;
use App\Gsuite;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
	protected $client;
	protected $directory;
	protected $classroom;

    function __construct() {
		$this->client = new \Google_Client();
		$this->client->setAuthConfig(Config::get('google.service_auth_file'));
		$this->client->setApplicationName(Config::get('google.application_name'));
		$this->client->setScopes(Config::get('google.scopes'));
		$this->client->setSubject(Config::get('google.admin'));
		$this->directory = new \Google_Service_Directory($this->client);
		$this->classroom = new \Google_Service_Classroom($this->client);
	}

	public function listOrgUnits()
	{
		try {
			$result = $this->directory->orgunits->listOrgunits('my_customer');
			return $result->getOrganizationUnits();
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function getOrgUnit($orgPath)
	{
		try {
			return $this->directory->orgunits->get('my_customer', $orgPath);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function createOrgUnit($orgPath, $orgName, $orgDescription)
	{
		$org_unit = new \Google_Service_Directory_OrgUnit();
		$org_unit->setName($orgName);
		$org_unit->setDescription($orgDescription);
		$org_unit->setParentOrgUnitPath($orgPath);
		try {
			return $this->directory->orgunits->insert('my_customer', $org_unit);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function updateOrgUnit($orgPath, $orgName)
	{
		$org_unit = new \Google_Service_Directory_OrgUnit();
		$org_unit->setDescription($orgName);
		try {
			return $this->directory->orgunits->update('my_customer', $orgPath, $org_unit);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function deleteOrgUnit($orgPath)
	{
		try {
			return $this->directory->orgunits->delete('my_customer', $orgPath);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function delegatedAdmin($userID, $orgID)
	{
		$roleID = '10283934923358215';
		$role_assign = new \Google_Service_Directory_RoleAssignment();
		$role_assign->setScopeType('ORG_UNIT');
		$role_assign->setOrgUnitId($orgID);
		$role_assign->setRoleId($roleID);
		$role_assign->setAssignedTo($userID);
		try {
			return $this->directory->roleAssignments->insert('my_customer', $role_assign);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function findUsers($filter)
	{
		try {
			$result = $this->directory->users->listUsers(array( 'domain' => Config::get('saml.email_domain'), 'query' => $filter));
			return $result->getUsers();
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	// userKey may be $user->nameID() or their gmail address.(nameID with saml.email_domain)
	public function getUser($userKey)
	{
		if (!strpos($userKey, '@')) $userKey .= '@'. Config::get('saml.email_domain');
		try {
			return $this->directory->users->get($userKey);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function createUser($userObj)
	{
		try {
			return $this->directory->users->insert($userObj);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function updateUser($userKey, $userObj)
	{
		try {
			return $this->directory->users->update($userKey, $userObj);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function deleteUser($userKey)
	{
		if (!strpos($userKey, '@')) $userKey .= '@'. Config::get('saml.email_domain');
		try {
			return $this->directory->users->delete($userKey);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function transferUser($new_domain, $userKey)
	{
		if (!strpos($userKey, '@')) return;
		try {
			$userObj = $this->directory->users->get($userKey);
			$nameID = explode('@', $userKey);
			$newKey = $nameID[0].'@'.$new_domain;
			return $this->directory->users->update($userKey, $userObj);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function sync(User $user)
	{
		$new_user = false;
		if ($user->nameID()) {
			$gmail = $user->nameID() .'@'. Config::get('saml.email_domain');
			$gsuite_user = $this->getUser($gmail);
			if (!$gsuite_user) $new_user = true;
		} else $new_user = true;
		if ($new_user) {
			$gsuite_user = new \Google_Service_Directory_User();
			$gsuite_user->setChangePasswordAtNextLogin(false);
			$gsuite_user->setAgreedToTerms(true);
			$nameID = $user->account();
			if (!empty($nameID) && !$user->is_default_account()) {
				$gmail = $nameID .'@'. Config::get('saml.email_domain');
				$gsuite_user->setPrimaryEmail($gmail);
				$gsuite_user->setPassword($user->uuid);
			} else {
				return false;
			}
		}
		if ($user->email) $gsuite_user->setRecoveryEmail($user->email);
		$phone = new \Google_Service_Directory_UserPhone();
		if ($user->mobile) {
			$phone->setPrimary(true);
			$phone->setType('mobile');
			$phone->setValue($user->mobile);
			$phones[] = $phone;
			$gsuite_user->setPhones($phones);
		}
		$names = new \Google_Service_Directory_UserName();
		$names->setFamilyName($user->ldap['sn']);
		$names->setGivenName($user->ldap['givenName']);
		$names->setFullName($user->name);
		$gsuite_user->setName($names);
		$gender = new \Google_Service_Directory_UserGender();
		if (!empty($user->ldap['gender'])) {
			switch ($user->ldap['gender']) {
				case 0:
					$gender->setType('unknow');
					break;
				case 1:
					$gender->setType('male');
					break;
				case 2:
					$gender->setType('female');
					break;
				case 9:
					$gender->setType('other');
					break;
			}
		}
		$gsuite_user->setGender($gender);
		$gsuite_user->setIsAdmin($user->is_admin ? true : false);
		if (!empty($user->ldap['o'])) {
			$orgs = array();
			$orgIds = array();
			if (is_array($user->ldap['o'])) {
				$orgs = $user->ldap['o'];
			} else {
				$orgs[] = $user->ldap['o'];
			}
			if (is_array($user->ldap['adminSchools'])) {
				$orgs = array_values(array_unique(array_merge($orgs, $user->ldap['adminSchools'])));
			}
			foreach ($orgs as $org) {
				$org_title = $user->ldap['school'][$org];
				$org_unit = $this->getOrgUnit($org);
				if (!$org_unit) {
					$org_unit = $this->createOrgUnit('/', $org, $org_title);
					if (!$org_unit) return false;
				}
				$orgIds[$org] = substr($org_unit->getOrgUnitId(), 3);
			}
			if ($user->ldap['employeeType'] == '學生') {
				if (!$this->getOrgUnit($orgs[0] .'/students')) {
					if (!$this->createOrgUnit('/'.$orgs[0], 'students', '學生')) return false;
				}
				$gsuite_user->setOrgUnitPath('/'. $orgs[0] .'/students');
			} else {
				$gsuite_user->setOrgUnitPath('/'.$orgs[0]);
			}
		}
		// Google is not support bcrypt yet!! so we can't sync password to g-suite!
		// $gsuite_user->setPassword($user->password);
		// $gsuite_user->setHashFunction('bcrypt');
		if (!$new_user) {
			$result = $this->updateUser($gmail, $gsuite_user);
		} else {
			if ($result = $this->createUser($gsuite_user)) {
				$gsuite = new Gsuite();
				$gsuite->idno = $user->idno;
				$gsuite->nameID = $nameID;
				$gsuite->primary = true;
				$gsuite->save();
			}
		}
		if ($result) {
			if (is_array($user->ldap['adminSchools'])) {			
				$userID = $result->getId();
				foreach ($user->ldap['adminSchools'] as $org) {
					$orgID = $orgIds[$org];
					$this->delegatedAdmin($userID, $orgID);
				}
			}
			return true;
		}
		return false;
	}

	public function createUserAlias($email, $alias)
	{
		$email_alias = new \Google_Service_Directory_Alias();
		$email_alias->setAlias($alias);
		try {
			return $this->directory->users_aliases->insert($email,$email_alias);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function listUserAliases($email)
	{
		try {
			return $this->directory->users_aliases->listUsersAliases($email);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function removeUserAlias($email, $alias)
	{
		try {
			return $this->directory->users_aliases->delete($email, $alias);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function listGroups()
	{
		try {
			return $this->directory->groups->listGroups();
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function listMembers($groupId)
	{
		try {
			return $this->directory->members->listMembers($groupId);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function addMembers($groupId, $members)
	{
		$users = array();
		foreach ($members as $m) {
			$member = new \Google_Service_Directory_Member();
			$member->setEmail($m);
			$member->setRole('MEMBER');
			try {
				$users[] = $this->directory->members->insert($groupId, $member);
			} catch (\Google_Service_Exception $e) {
				if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			}
		}
		return $users;
	}

	public function listCourses($teacher)
	{
		try {
			return $this->classroom->courses->listCourses(['teacherId' => $teacher])->getCourses();
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function getCourse($courseId)
	{
		try {
			return $this->classroom->courses->get($courseId);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function createCourse($name, $ownerId, $section, $descriptionHeading, $description, $room, $courseState)
	{
		$course = new \Google_Service_Classroom_Course();
		$course->setName($name);
		//OwnerId: id, email or 'me'
		$course->setOwnerId($ownerId);
		if (!empty($section)) $course->setSection($section);
		if (!empty($descriptionHeading)) $course->setDescriptionHeading($descriptionHeading);
		if (!empty($description)) $course->setDescription($description);
		if (!empty($room)) $course->setRoom($room);
		//CourceState: PROVISIONED,ACTIVE,DECLINED
		if(!empty($courseState)) $course->setCourseState($courseState);
		try {
			return $this->classroom->courses->create($course);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function deleteCourse($courseId)
	{
		try {
			return $this->classroom->courses->delete($courseId);
		} catch (\Google_Service_Exception $e) {
			if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			return false;
		}
	}

	public function addCourseTeachers($courseId, $teachers)
	{
		$users = array();
		foreach($teachers as $t){
			$tea = new \Google_Service_Classroom_Teacher();
			$tea->setUserId($t);
			try {
				$users[] = $this->classroom->courses_teachers->create($courseId, $tea);
			} catch (\Google_Service_Exception $e) {
				if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			}
		}
		return $users;
	}

	public function removeCourseTeachers($courseId, $teachers)
	{
		$users = array();
		foreach($teachers as $t){
			try {
				$users[] = $this->classroom->courses_teachers->delete($courseId, $t);
			} catch (\Google_Service_Exception $e) {
				if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			}
		}
		return $users;
	}

	public function addCourseStudents($courseId, $students)
	{
		$users = array();
		foreach($students as $s){
			$stu = new \Google_Service_Classroom_Student();
			$stu->setUserId($s);
			try {
				$users[] = $this->classroom->courses_students->create($courseId, $stu);
			} catch (\Google_Service_Exception $e) {
				if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			}
		}
		return $users;
	}

	public function removeCourseStudents($courseId, $students)
	{
		$users = array();
		foreach($students as $s){
			try {
				$users[] = $this->classroom->courses_students->delete($courseId, $s);
			} catch (\Google_Service_Exception $e) {
				if (Config::get('google.debug')) Log::debug('Google Service Caught exception: '.  $e->getMessage() ."\n");
			}
		}
		return $users;
	}

}