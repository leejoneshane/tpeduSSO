<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OauthSocialiteAccount extends Model
{
    protected $table = 'oauth_socialite_account';
    public $timestamps = true;
    
    public static function add($source, $idno, $oauth_id, $email) {
		$log = new OauthSocialiteAccount;
		$log->source = $source;
		$log->idno = $idno;
		$log->oauth_id = $oauth_id;
		$log->email = $email;
		$log->save();
    }
}