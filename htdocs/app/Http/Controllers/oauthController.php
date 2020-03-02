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
        if ($user->is_parent) return redirect()->route('parent');
        $tokens = Passport::token()->where('user_id', $user->getKey())->get();
        $mytokens =  $tokens->load('client')->filter(function ($token) {
            return ! $token->client->firstParty() && ! $token->revoked;
        })->values();
        return view('auth.oauthManager', [ 'tokens' => $mytokens ]);
    }

    public function revokeToken(Request $request, $token_id)
    {
        $user = Auth::user();
        if ($user->is_parent) return redirect()->route('parent');
        $token = Passport::token()->where('id', $token_id)->where('user_id', $user->getKey())->first();
        $token->revoke();
        return redirect()->route('oauth');
    }

    public function socialite(Request $request)
    {
        $user = Auth::user();
        $query = $user->socialite_accounts();
        $google = $query->where('socialite', 'Google')->first();
        $facebook = $query->where('socialite', 'Facebook')->first();
        $yahoo = $query->where('socialite', 'Yahoo')->first();
        $line = $query->where('socialite', 'Line')->first();
        return view('auth.socialiteManager', [ 'google' => $google, 'facebook' => $facebook, 'yahoo' => $yahoo, 'line' => $line ]);
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