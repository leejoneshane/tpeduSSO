<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Providers\LdapServiceProvider;
use App\User;

class PSLink extends Model
{
    protected $table = 'parent_student_link';

    protected $fillable = [
        'parent_idno', 'student_idno', 'relation', 'verified', 'verified_idno', 'denyReason', 'verified_time',
    ];

    protected $casts = [
		'verified' => 'boolean',
    ];

	public function parent()
	{
    	return User::where('idno', $this->attributes['parent_idno'])->first();
    }

    public function student()
	{
    	return User::where('idno', $this->attributes['student_idno'])->first();
    }
    
    public function verified_by()
	{
    	return User::where('idno', $this->attributes['verified_idno'])->first();
	}

    public static function byClass($dc, $ou)
	{
		$openldap = new LdapServiceProvider();
		$filter = "(&(o=$dc)(tpClass=$ou)(employeeType=學生)(!(inetUserStatus=deleted)))";
		$students = $openldap->findUsers($filter, 'cn');
        if (!$students) return false;
        $users = array();
        foreach ($students as $s) {
            $users[] = $s['cn'];
        }
    	return PSLink::whereIn('student_idno', $users)->orderBy('verified')->get();
	}

}