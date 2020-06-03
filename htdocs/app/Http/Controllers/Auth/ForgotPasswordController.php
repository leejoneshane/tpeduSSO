<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $user = $this->broker()->getUser($this->credentials($request));
        if ($user->hasVerifiedEmail()) {
            $response = $this->broker()->sendResetLink($this->credentials($request));
            return $response == Password::RESET_LINK_SENT
                        ? $this->sendResetLinkResponse($request, $response)
                        : $this->sendResetLinkFailedResponse($request, $response);    
        } else {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => '您的電子郵件尚未經過驗證，因此無法用於接收密碼重置信函，請逕洽貴校資訊組或導師為您還原密碼！']);
        }
    }
}
