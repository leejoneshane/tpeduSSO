<?php
namespace App\Providers;

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
		$this->client->useApplicationDefaultCredentials();
		$this->client->setApplicationName(Config::get('google.application_name'));
		$this->client->setScopes(Config::get('google.scopes'));
		$this->client->setAuthConfig(Config::get('google.service_auth_file'));
		$this->client->setSubject(Config::get('google.admin'));
        $this->directory = new \Google_Service_Directory($this->client);
		$this->classroom = new \Google_Service_Classroom($this->client);
		
        if ($this->client->getAuth()->isAccessTokenExpired()) {
          $this->client->getAuth()->fetchAccessTokenWithRefreshToken();
        }
	}
	
	public function listUsers($filter)
	{
		$result = $this->directory->users->listUsers(array( 'query' => $filter));
		if (!empty($result) && array_key_exists('users',$result)) return $result->users;
		return null;
	}

	public function getUser($email)
	{
		return $this->directory->users->get($email);
	}

	public function createUser(User $user)
	{
		$gsuite_user = new \Google_Service_Directory_User();
		$names = new \Google_Service_Directory_UserName();
		$gender = new \Google_Service_Directory_UserGender();
		$phone = new \Google_Service_Directory_UserPhone();
		$gsuite_user->setKind("admin#directory#user");
		$gsuite_user->setChangePasswordAtNextLogin(false);
		$gsuite_user->setAgreedToTerms(true);
		$accounts = array();
		if (is_array($user->ldap['uid'])) {
			$accounts = $user->ldap['uid'];
		} else {
			$accounts[] = $user->ldap['uid'];
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
		$nameID = array_values($accounts)[0];
		$gsuite_user->setPrimaryEmail($nameID .'@'. Config::get('saml.email_domain'));
		if ($user->email) $gsuite_user->setRecoveryEmail($user->email);
		if ($user->mobile) {
			$phone->setPrimary(true);
			$phone->setType('mobile');
			$phone->setValue($user->mobile);
			$phones[] = $phone;
			$gsuite_user->setPhones($phones);
		}
		$gsuite_user->setIsAdmin(false);
		$names->setFamilyName($user->ldap['sn']);
		$names->setGivenName($user->ldap['givenName']);
		$names->setFullName($user->name);
		$gsuite_user->setName($names);
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
		$gsuite_user->setGender($gender);
		$gsuite_user->setPassword($user->password);
		$gsuite_user->setHashFunction('crypt');
		$result = $this->directory->users->insert($gsuite_user);
		if ($result) {
			$gsuite = new Gsuite();
			$gsuite->idno = $user->idno;
			$gsuite->nameID = $nameID;
			$gsuite->primary = 1;
			$gsuite->save();
		}
	}

	public function createUserAlias($email, $alias)
	{
		$email_alias = new \Google_Service_Directory_Alias();
		$email_alias->setAlias($alias);
		return $this->directory->users_aliases->insert($email,$email_alias);
	}

	public function findUserAlias($email)
	{
		return $this->directory->users_aliases->listUsersAliases($email);
	}

	public function removeUserAlias($email, $alias)
	{
		return $this->directory->users_aliases->delete($email, $alias);
	}

	public function listGroups()
	{
		return $this->directory->groups->listGroups();
	}

	public function listMembers($groupId)
	{
		return $this->directory->members->listMembers($groupId);
	}

	public function groupAddMembers($groupId, $members)
	{
		$users = array();
		foreach ($members as $m) {
			$member = new \Google_Service_Directory_Member();
			$member->setEmail($m);
			$member->setRole('MEMBER');
			$users[] = $this->directory->members->insert($groupId, $member);
		}
		return $users;
	}

	public function listCourses()
	{
		return $this->classroom->courses->listCourses();
	}

	public function getCourse($courseId)
	{
		return $this->classroom->courses->get($courseId);
	}

	public function createCourse($name, $ownerId, $section, $descriptionHeading, $description, $room, $courseState)
	{
		$course = new \Google_Service_Classroom_Course();
		$course->setName($name);
		$course->setOwnerId($ownerId);
		if(!empty($section)) $course->setSection($section);
		if(!empty($descriptionHeading)) $course->setDescriptionHeading($descriptionHeading);
		if(!empty($description)) $course->setDescription($description);
		if(!empty($room)) $course->setRoom($room);
		//PROVISIONED,ACTIVE,DECLINED
		if(!empty($courseState)) $course->setCourseState($courseState);
		return $this->classroom->courses->create($course);
	}

	public function deleteCourse($courseId)
	{
		return $this->classroom->courses->delete($courseId);
	}

	public function addCourseTeachers($courseId, $teachers)
	{
		$users = array();
		foreach($teachers as $t){
			$tea = new \Google_Service_Classroom_Teacher();
			$tea->setUserId($t);
			$users[] = $this->classroom->courses_teachers->create($courseId, $tea);
		}
		return $users;
	}

	public function removeCourseTeachers($courseId, $teachers)
	{
		$users = array();
		foreach($teachers as $t){
			$users[] = $this->classroom->courses_teachers->delete($courseId, $t);
		}
		return $users;
	}

	public function addCourseStudents($courseId, $students)
	{
		$users = array();
		foreach($students as $s){
			$stu = new \Google_Service_Classroom_Student();
			$stu->setUserId($s);
			$users[] = $this->classroom->courses_students->create($courseId, $stu);
		}
		return $users;
	}

	public function removeCourseStudents($courseId, $students)
	{
		$users = array();
		foreach($students as $s){
			$users[] = $this->classroom->courses_students->delete($courseId, $s);
		}
		return $users;
	}
}