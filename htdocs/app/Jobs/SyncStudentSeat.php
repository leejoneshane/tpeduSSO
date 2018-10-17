<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Providers\LdapServiceProvider;
use App\Providers\SimsServiceProvider;

class SyncSchoolInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $dc;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dc)
    {
        $this->dc = $dc;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 3) {
            $this->delete();
        }
		$openldap = new LdapServiceProvider();
		$http = new SimsServiceProvider();
		$sid = $openldap->getOrgID($this->dc);
		$students = $openldap->findUsers("(&(o=$this->dc)(employeeType=學生))", ["cn", "o", "displayName", "employeeNumber", "tpClass", "tpSeat"]);
		foreach ($students as $stu) {
			$stdno = $stu['employeeNumber'];
			$data = $http->ps_call('student_info', [ '{sid}' => $sid, '{stdno}' => $stdno ]);
			if ($data) {
                $c = (int) $data[0]->class;
                $s = (int) $data[0]->seat;
				$user_entry = $openldap->getUserEntry($stu['cn']);
				if (substr($c, 0, 1) == 'Z') {
					$openldap->updateData($user_entry, [ 'inetUserStatus' => 'deleted' ]);
				} else {
					$openldap->updateData($user_entry, [ 'tpClass' => $c, 'tpSeat' => $s ]);
				}
			} else {
                $this->release(3600);
			}
		}
	}
}
