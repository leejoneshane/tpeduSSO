<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class OauthController extends Controller
{
    public function __construct()
    {
        //
    }
    public function index()
    {
        return view('auth.oauthManager');
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
        $this->socialite();
    }

}