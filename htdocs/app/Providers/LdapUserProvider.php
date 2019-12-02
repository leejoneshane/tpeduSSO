<?php

namespace App\Providers;

use App\User;
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

		if(isset($id) && !empty($id)){
			$entry = $this->openLDAP->getUserEntry($id);
			$data = $this->openLDAP->getUserData($entry);
		}else if (isset($credentials['username'])) {
			//查家長資料
			$par = \App\ParentsInfo::where('cn',$credentials['username'])->first();
			if($par){
				$id = $par->cn;
				$data = ['entryUUID' => $par->uuid, 'uid' => $par->mail, 'displayName' => $par->display_name, 'mail' => $par->mail, 'mobile' => $par->mobile];
			}
		}

		if (isset($data) && !empty($data)) {
			$model = parent::createModel();
			$user = User::where('idno', $id)->first();
			if (!$user) $user = User::where('uuid', $data['entryUUID'])->first();
			if (!$user) {
				$user = new User();
				$user->idno = $id;
				if (isset($data['uid'])) {
					if (is_array($data['uid'])) {
						$user->uname = $data['uid'][0];
					} else {
						$user->uname = $data['uid'];
					}
				}
				$user->name = $data['displayName'];
				$user->uuid = $data['entryUUID'];
				if (isset($credentials['email'])) {
					$user->email = $credentials['email'];
				} elseif (!empty($data['mail'])) {
					if (is_array($data['mail'])) {
						$user->email = $data['mail'][0];
					} else {
						$user->email = $data['mail'];
					}
					if (!$this->openLDAP->emailAvailable($id, $user->email)) $user->email = null;
				} else $user->email = null;
				if (!empty($data['mobile'])) {
					if (is_array($data['mobile'])) {
						$user->mobile = $data['mobile'][0];
					} else {
						$user->mobile = $data['mobile'];
					}
				} else $user->mobile = null;
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
		if (isset($credentials['username'])) {
			if (substr($credentials['username'],0,3) == 'cn=') {
				return $this->openLDAP->userLogin($credentials['username'], $credentials['password']);
			} else {
				$flag = $this->openLDAP->authenticate($credentials['username'], $credentials['password']);
				if(!$flag)
					$flag = count(\App\ParentsInfo::where('cn',$credentials['username'])->get()) > 0;
				return $flag;
			} 
		}	
	}
}
