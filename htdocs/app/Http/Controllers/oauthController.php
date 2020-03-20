<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

class OauthController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        if ($user->is_parent) return redirect()->route('parent');
        $tokens = Passport::token()->where('user_id', $user->getKey())->where('revoked', 0)->where('expires_at', '>', Carbon::now())->get();
        $mytokens =  $tokens->load('client')->filter(function ($token) {
            return ! $token->client->firstParty();
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

}