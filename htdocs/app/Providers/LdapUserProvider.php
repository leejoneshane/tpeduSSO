<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\Providers\LdapServiceProvider;

class LdapUserProvider extends EloquentUserProvider
{
    protected $openLDAP;
    
    public function __construct(HasherContract $hasher, $model)
    {
        $this->model = $model;
        $this->hasher = $hasher;
	$this->openLDAP = new LdapServiceProvider();
    }

    public function retrieveByCredentials(array $credentials)
    {
	if (empty($credentials)) return;
	
	if (isset($credentials['username'])) {
	    if (substr($credentials['username'],0,3) == 'cn=') {
		$id = $this->openLDAP->checkIdno($credentials['username']);
	    } else {
		$id = $this->openLDAP->checkAccount($credentials['username']);
	    }
	}
	if (isset($credentials['email'])) {
	    $id = $this->openLDAP->checkEmail($credentials['email']);
	}
	if (isset($credentials['mobile'])) {
	    $id = $this->openLDAP->checkMobile($credentials['mobile']);
	}
	if ($id) {
	    $model = parent::createModel();
	    $user = $model->newQuery()
	        ->where('idno', $id)
	        ->first();
	    if (is_null($user)) {
		$entry = $this->openLDAP->getUserEntry($id);
		$data = $this->openLDAP->getUserData($entry);
	        $user = new \App\User();
	        $user->idno = $id;
	        $user->name = $data['displayName'];
		$user->uuid = $data['entryUUID'];
	        if (isset($data['mail'])) {
		    $user->email = $data['mail'];
		} else {
		    $user->email = null;
		}
	        if (isset($data['mobile'])) {
		    $user->mobile = $data['mobile'];
		} else {
		    $user->mobile = null;
		}
		if (isset($credentials['password'])) {
	    	    $user->password = \Hash::make($credentials['password']);
	        } else {
	    	    $user->password = \Hash::make(substr($id,-6));
	        }
	        $user->save();
	    }
	    return $user;
	}
    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
	if (substr($credentials['username'],0,3) == 'cn=') {
	    return $this->openLDAP->userLogin($credentials['username'], $credentials['password']);
	} else {
	    return $this->openLDAP->authenticate($credentials['username'], $credentials['password']);
	}
    }
}
