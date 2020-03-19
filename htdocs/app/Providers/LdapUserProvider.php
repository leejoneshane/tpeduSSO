<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\Providers\LdapServiceProvider;
use App\User;

class LdapUserProvider extends EloquentUserProvider
{

    public function retrieveByCredentials(array $credentials)
    {
		if (empty($credentials) ||
           (count($credentials) === 1 &&
            array_key_exists('password', $credentials))) {
            return;
		}
		$openldap = new LdapServiceProvider();
		$id = false;
		if (isset($credentials['username'])) {
			if (substr($credentials['username'],0,3) == 'cn=') {
				$idno = substr($credentials['username'],3);
				if ($user = User::where('idno', $idno)->first()) return $user;
				$id = $openldap->checkIdno($credentials['username']);
			} else {
				if ($user = User::where('is_parent', 1)->where('email', $credentials['username'])->first()) return $user;
				$id = $openldap->checkAccount($credentials['username']);
			}
		}
		if (isset($credentials['email'])) {
			if ($user = User::where('email', $credentials['email'])->first()) return $user;
			$id = $openldap->checkEmail($credentials['email']);
		}
		if (isset($credentials['mobile'])) {
			if ($user = User::where('mobile', $credentials['mobile'])->first()) return $user;
			$id = $openldap->checkMobile($credentials['mobile']);
		}
		if ($id) {
			$new = false;
			$entry = $openldap->getUserEntry($id);
			$data = $openldap->getUserData($entry);
			$user = User::where('idno', $id)->first();
			if (is_null($user)) {
				$user = $this->createModel();
				$user->idno = $id;
				$user->uuid = $data['entryUUID'];
				$new = true;
			}
			if (isset($credentials['password'])) {
				$user->password = \Hash::make($credentials['password']);
			} else {
				$user->password = \Hash::make(substr($id,-6));
			}
			$user->name = $data['displayName'];
			if (!empty($data['mail'])) {
				if (is_array($data['mail']))
					$email = $data['mail'][0];
				else
					$email = $data['mail'];
				if (User::where('idno', '!=', $id)->where('email', $email)->exists()) $email = false;
				if ($email && $openldap->emailAvailable($id, $email)) $user->email = $email;
			}
			if (!empty($data['mobile'])) {
				if (is_array($data['mobile']))
					$mobile = $data['mobile'][0];
				else
					$mobile = $data['mobile'];
				if (User::where('idno', '!=', $id)->where('mobile', $mobile)->exists()) $mobile = false;
				if ($mobile && $openldap->mobileAvailable($id, $mobile)) $user->mobile = $mobile;
			}
			if ($new && User::where('uuid', $user->uuid)->exists()) return;
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
