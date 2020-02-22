<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Providers\LdapServiceProvider;
use App\User;
use Laravel\Passport\Passport;

class PSAuthorize extends Model
{
    protected $table = 'parent_student_authorize';

    protected $fillable = [
        'parent_idno', 'student_idno', 'client_id', 'trust_level',
    ];

	public function parent()
	{
        if ($this->attributes['parent_idno'] == 'qrcode') return false;
    	return User::where('idno', $this->attributes['parent_idno'])->first();
    }

    public function student()
	{
    	return User::where('idno', $this->attributes['student_idno'])->first();
    }
    
    public function client()
	{
        $clients = Passport::client();
        $client = $clients->where($clients->getKeyName(), $this->attributes['client_id'])->first();
        return $client;
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
    	return PSAuthorize::whereIn('student_idno', $users)->where('trust_level', '>', 0)->get();
	}

}