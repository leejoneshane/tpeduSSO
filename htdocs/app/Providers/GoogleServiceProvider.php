<?php
namespace App\Providers;

use Config;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
	protected $client;
	protected $directory;
	protected $classroom;

    function __construct() {
		$this->client = new \Google_Client();
		$this->client->setApplicationName(Config::get('google.application_name'));
		$this->client->setScopes(Config::get('google.scopes'));
		$this->client->setAuthConfig(Config::get('google.service_auth_file'));
		$this->client->setSubject('sean@ms.tp.edu.tw');
        $this->directory = new \Google_Service_Directory($this->client);
		$this->classroom = new \Google_Service_Classroom($this->client);
		
        if ($this->client->getAuth()->isAccessTokenExpired()) {
          $this->client->getAuth()->fetchAccessTokenWithRefreshToken();
        }
	}
	
	public function findUsers($filter)
	{
		$result = $this->directory->users->listUsers(array( 'query' => $filter));
		if (!empty($result) && array_key_exists('users',$result)) return $result->users;
		return null;
	}

	public function getUser($email)
	{
		return $this->directory->users->get($email);
	}

	public function createUser($uname, $familyName, $givenName, $fullName, $orgpath, $password)
	{
		$user = new \Google_Service_Directory_User();
		$names = new \Google_Service_Directory_UserName();
		$user->setKind("admin#directory#user");
		$user->setChangePasswordAtNextLogin(false);
		$user->setPrimaryEmail($uname.'@'. Config::get('saml.email_domain'));
		$user->setIsAdmin(false);
		$names->setFamilyName($familyName);
		$names->setGivenName($givenName);
		$names->setFullName($fullName);
		$user->setName($names);
		$user->setOrgUnitPath($orgpath);
		$user->setPassword($password);
		//$user->setOrgUnitPath('/');
		return $this->directory->users->insert($user);
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

	public function getTeacherGroup()
	{
		return $this->directory->groups->get('classroom_teachers@'.Config::get('saml.email_domain'));
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

	public function findCourses()
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