<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialiteAccount extends Model
{
    protected $table = 'socialite_account';

    protected $fillable = [
        'idno', 'socialite', 'userID',
    ];
    
	public function user()
	{
    	return $this->belongsTo('App\User', 'idno', 'idno');
	}
}