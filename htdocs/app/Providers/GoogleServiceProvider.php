<?php

namespace App\Providers;

use Log;
use Config;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
    public function __construct()
    {
    }

	public function getClient()
	{
		$KEY_FILE_LOCATION = storage_path('gsuite/service-account-credentials.json');

		$client = new \Google_Client();
		//$client->setApplicationName('api-test');
		//$client->setDeveloperKey("109465977024326193231");
		$client->setAuthConfig($KEY_FILE_LOCATION);
		//$client->useApplicationDefaultCredentials();
		//If you have delegated domain-wide access to the service account and you want to impersonate a user account
		//specify the email address of the user account using the method setSubject
		$client->setSubject('leosys@gm.tp.edu.tw');

		return $client;
	}

	public function queryUserByEmail($email)
	{
		Log::debug('query g-suite email:'.$email);

		$client = $this::getClient();
		$optParams = array('customer' => 'my_customer','maxResults' => 20,'orderBy' => 'email','query' => 'email='.$email);
		//$client->setScopes(['https://www.googleapis.com/auth/admin.directory.user']);
		$client->addScope(\Google_Service_Directory::ADMIN_DIRECTORY_USER);
		$service = new \Google_Service_Directory($client);
		//查單人
		//$results = $service->users->get('test@gm.tp.edu.tw', ['fields' => 'primaryEmail,name']);
		//查全部
		$result = $service->users->listUsers($optParams);

		if(!empty($result) && array_key_exists('users',$result))
			return $result->users;
		return null;
	}

	public function createUserAccount($uname, $familyName, $givenName, $fullName, $password)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Directory::ADMIN_DIRECTORY_USER);
		$service = new \Google_Service_Directory($client);
		$user = new \Google_Service_Directory_User($client);
		$names = new \Google_Service_Directory_UserName($client);

		$user->setKind("admin#directory#user");
		$user->setChangePasswordAtNextLogin(false);
		$user->setprimaryEmail($uname.'@'.'gm.tp.edu.tw');//env('SAML_MAIL', 'gm.tp.edu.tw'));
		$user->setIsAdmin(false);
		$names->setFamilyName($familyName);
		$names->setGivenName($givenName);
		$names->setFullName($fullName);
		$user->setName($names);
		$user->setPassword($password);
		//$user->setEmails(array("address"=>'annihir@gm.tp.edu.tw', "type"=>"work", "primary"=>true, "isAdmin"=>false));
		//$user->setOrgUnitPath('/');

		return $service->users->insert($user);
	}

	public function createUserAlias($email, $alias)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Directory::ADMIN_DIRECTORY_USER_ALIAS);
		$service = new \Google_Service_Directory($client);
		return $service->users_aliases->insert($email, new \Google_Service_Directory_Alias(['alias' => $alias]));
	}

	public function queryUserAlias($email)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Directory::ADMIN_DIRECTORY_USER_ALIAS);
		$service = new \Google_Service_Directory($client);
		return $service->users_aliases->listUsersAliases($email);
	}

	public function deleteUserAlias($email, $alias)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Directory::ADMIN_DIRECTORY_USER_ALIAS);
		$service = new \Google_Service_Directory($client);
		return $service->users_aliases->delete($email, $alias);
	}

	public function queryGroup()
	{
		$client = $this::getClient();
		$optParams = array('customer' => 'my_customer','maxResults' => 20);//,'orderBy' => 'email','query' => 'email='.$email);
		$client->addScope(\Google_Service_Directory::ADMIN_DIRECTORY_GROUP);
		$service = new \Google_Service_Directory($client);
		return $service->groups->listGroups($optParams);
	}

	public function groupAddMembers($groupId, $members)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER);
		$service = new \Google_Service_Directory($client);

		$user = [];
		foreach($members as $m){
			try{
				$member = new \Google_Service_Directory_Member();
				$member->setEmail($m);
				$member->setRole('MEMBER');
				$user[] = $service->members->insert($groupId, $member);
			}catch(\Exception $e){
				$user[] = json_decode($e->getMessage());
			}
		}

		return $user;
	}

	public function createCourse($name, $section, $descriptionHeading, $description, $room, $ownerId, $courseState)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Classroom::CLASSROOM_COURSES);
		$service = new \Google_Service_Classroom($client);

		//ownerId:id, email or 'me'
		$course = ['name' => $name, 'ownerId' => $ownerId];
		if(!empty($section)) $course['section'] = $section;
		if(!empty($descriptionHeading)) $course['descriptionHeading'] = $descriptionHeading;
		if(!empty($description)) $course['description'] = $description;
		if(!empty($room)) $course['room'] = $room;
		//PROVISIONED,ACTIVE,DECLINED
		if(!empty($courseState)) $course['courseState'] = $courseState;

		return $service->courses->create(new \Google_Service_Classroom_Course($course));
	}

	public function deleteCourse($courseId)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Classroom::CLASSROOM_COURSES);
		$service = new \Google_Service_Classroom($client);
		return $service->courses->delete($courseId);
	}

	public function queryCourse()
	{
		$client = $this::getClient();
		$optParams = array('pageSize' => 20);
		$client->addScope(\Google_Service_Classroom::CLASSROOM_COURSES);
		$service = new \Google_Service_Classroom($client);
		return $service->courses->listCourses($optParams);
	}

	public function classroomAddTeachers($courseId, $teachers)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Classroom::CLASSROOM_ROSTERS);
		$service = new \Google_Service_Classroom($client);

		$user = [];
		foreach($teachers as $tea){
			try{
				$user[] = $service->courses_teachers->create($courseId, new \Google_Service_Classroom_Teacher(array('userId' => $tea)));
			}catch(\Exception $e){
				$user[] = json_decode($e->getMessage());
			}
		}

		return $user;
	}

	public function classroomEnrollStudents($courseId, $students)
	{
		$client = $this::getClient();
		$client->addScope(\Google_Service_Classroom::CLASSROOM_ROSTERS);
		$service = new \Google_Service_Classroom($client);

		$user = [];
		foreach($students as $stu){
			try{
				$user[] = $service->courses_students->create($courseId, new \Google_Service_Classroom_Student(array('userId' => $stu)));
			}catch(\Exception $e){
				$user[] = json_decode($e->getMessage());
			}
		}

		return $user;
	}
}