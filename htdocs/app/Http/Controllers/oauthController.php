<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

class OauthController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $tokens = Passport::token()->where('user_id', $user->getKey())->get();
        $mytokens =  $tokens->load('client')->filter(function ($token) {
            return ! $token->client->firstParty() && ! $token->revoked;
        })->values();
        $is_schoolAdmin = false;
        $pstokens = array();
        if (isset($user->ldap['adminSchools'])) {
            $is_schoolAdmin = true;
            $pstokens = $tokens->load('client')->filter(function ($token) {
                return $token->client->personal_access_client && ! $token->revoked;
            })->values();
        }
        return view('auth.oauthManager', [ 'is_schoolAdmin' => $is_schoolAdmin, 'tokens' => $mytokens, 'personal' => $pstokens ]);
    }

    public function revokeToken(Request $request, $token_id)
    {
        $user = Auth::user();
        $token = Passport::token()->where('id', $token_id)->where('user_id', $user->getKey())->first();
        $token->revoke();
        return redirect()->route('oauth');
    }

    public function showCreateTokenForm(Request $request)
    {
        $scopes = Passport::scopes();
        return view('auth.createTokenForm', [ 'scopes' => $scopes ]);
    }

    public function storeToken(Request $request)
    {
		$validatedData = $request->validate([
            'name' => 'required|max:255',
            'scopes' => 'array|in:'.implode(',', Passport::scopeIds()),
        ]);
        $token = Auth::user()->createToken($request->get('name'), $request->get('scopes') ?: []);
        return view('auth.showToken', [ 'token' => $token ]);
    }

    public function socialite(Request $request)
    {
        $user = Auth::user();
        $query = $user->socialite_accounts();
        $google = $query->where('socialite', 'Google')->first();
        $facebook = $query->where('socialite', 'Facebook')->first();
        $yahoo = $query->where('socialite', 'Yahoo')->first();
        return view('auth.socialiteManager', [ 'google' => $google, 'facebook' => $facebook, 'yahoo' => $yahoo ]);
    }

    public function removeSocialite(Request $request)
    {
        $user = Auth::user();
        $query = $user->socialite_accounts();
        $socialite = $request->get('socialite');
        $userid = $request->get('userid');
        $account = $query->where('socialite', $socialite)->where('userID', $userid)->delete();
        return redirect()->route('socialite');
    }

}