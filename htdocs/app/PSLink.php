<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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

}