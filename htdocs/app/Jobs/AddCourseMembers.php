<?php

namespace App\Jobs;

use Log;
use App\Providers\LdapServiceProvider;
use App\Providers\GoogleServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddCourseMembers implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

	protected $courseId;
	protected $user;
	protected $pid;
	protected $key;

    public function __construct($courseId, $user, $pid, $key)
    {
		$this->courseId = $courseId;
		$this->user = $user;
		$this->pid = $pid;
		$this->key = $key;
    }

    public function handle()
    {
		$openldap = new LdapServiceProvider();
		$gs = new GoogleServiceProvider();
		$domain = 'gm.tp.edu.tw';//env('SAML_MAIL', 'gm.tp.edu.tw'));

		//加入教師
		if(count($this->user)){
			$result = $gs->classroomAddTeachers($this->courseId, $this->user);
			//Log::debug('Add Classroom Teachers:'.json_encode($result,JSON_UNESCAPED_UNICODE));
		}

		//加入學生
		if(count($this->pid)){
			//篩掉沒修課的人
			$user = [];
			$stus = \App\StudentClasssubj::where('subjkey',$this->key)->whereIn('uuid',$this->pid)->get();
			foreach($stus as $stu)
				$user[] = $stu->uuid;

			for($i=count($this->pid)-1;$i>=0;$i--)
				if(!in_array($this->pid[$i],$user))
					unset($this->pid[$i]);

			//有G-Suite帳號的人
			if(!empty($this->pid))
				$stus = \App\User::whereIn('uuid',$this->pid)->whereNotNull('gsuite_created_at')->get();

			if(isset($stus) && !empty($stus)){
				$filter = '(|';
				foreach($stus as $stu)
					$filter .= '(cn='.$stu->idno.')';
				$filter .= ')';

				$user = [];
				$stus = $openldap->findAccounts($filter, ['uid']);
				if(!empty($stus)){
					foreach($stus as $stu)
						$user[] = $stu['uid'].'@'.$domain;

					$result = $gs->classroomEnrollStudents($this->courseId, $user);
					//Log::debug('Add Classroom Students:'.json_encode($result,JSON_UNESCAPED_UNICODE));
				}
			}
		}
    }

	public function failed()
    {
        // Called when the job is failing...
		Log::debug('AddCourseMembers failed:'.$this->courseId.','.$this->key);
    }
}