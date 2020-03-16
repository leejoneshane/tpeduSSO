<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\Providers\LdapServiceProvider;

class LdapUserProvider extends EloquentUserProvider
{

    public function retrieveByCredentials(array $credentials)
    {
		if (empty($credentials) ||
           (count($credentials) === 1 &&
            array_key_exists('password', $credentials))) {
            return;
		}
		$query = $this->newModelQuery();
		$openldap = new LdapServiceProvider();
		$id = false;
		if (isset($credentials['username'])) {
			if (substr($credentials['username'],0,3) == 'cn=') {
				$idno = substr($credentials['username'],3);
				if ($user = $query->where('idno', $idno)->first()) return $user;
				$id = $openldap->checkIdno($credentials['username']);
			} else {
				if ($user = $query->where('is_parent', 1)->where('email', $credentials['username'])->first()) return $user;
				$id = $openldap->checkAccount($credentials['username']);
			}
		}
		if (isset($credentials['email'])) {
			if ($user = $query->where('email', $credentials['email'])->first()) return $user;
			$id = $openldap->checkEmail($credentials['email']);
		}
		if (isset($credentials['mobile'])) {
			if ($user = $query->where('mobile', $credentials['mobile'])->first()) return $user;
			$id = $openldap->checkMobile($credentials['mobile']);
		}
		if ($id) {
			$entry = $openldap->getUserEntry($id);
			$data = $openldap->getUserData($entry);
			$user = $query->where('idno', $id)->first();
			if (is_null($user)) {
				$user = $this->createModel();
				$user->idno = $id;
				$user->uuid = $data['entryUUID'];
			}
			if (isset($credentials['password'])) {
				$user->password = \Hash::make($credentials['password']);
			} else {
				$user->password = \Hash::make(substr($id,-6));
			}
			$user->name = $data['displayName'];
			$user->email = null;
			if (!empty($data['mail'])) {
				if (is_array($data['mail']))
					$email = $data['mail'][0];
				else
					$email = $data['mail'];
			}
			if ($openldap->emailAvailable($id, $email)) $user->email = $email;
			if ($query->where('idno', '!=', $id)->where('email', $email)->exists()) $user->email = null;
			$user->mobile = null;
			if (!empty($data['mobile'])) {
				if (is_array($data['mobile']))
					$mobile = $data['mobile'][0];
				else
					$mobile = $data['mobile'];
			}
			if (!$openldap->mobileAvailable($id, $mobile)) $user->mobile = $mobile;
			if ($query->where('idno', '!=', $id)->where('mobile', $mobile)->exists()) $user->email = null;
			$user->save();
			return $user;
		}
	}

	public function validateCredentials(UserContract $user, array $credentials)
	{
		$openldap = new LdapServiceProvider();
		if (substr($credentials['username'],0,3) == 'cn=') {
			return $openldap->userLogin($credentials['username'], $credentials['password']);
		} else {
			if ($user->is_parent) 
				return $this->hasher->check($credentials['password'], $user->getAuthPassword());
			else
				return $openldap->authenticate($credentials['username'], $credentials['password']);
		}
	}

}
