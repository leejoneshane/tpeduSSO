<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use App\PSAuthorize;

class AboveAge12
{
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::user();
        if ($user->is_parent) return view('auth.parents');
        $age = Carbon::today()->subYears(13);
        $str = $user->ldap['birthDate'];
        $born = Carbon::createFromDate(substr($str,0,4), substr($str,4,2), substr($str,6,2), 'Asia/Taipei');
        if ($born <= $age) return $next($request);
        $client_id = $request->get('cient_id');
        $scopes = explode(' ', $request->query('scope'));
        $authorize = PSAuthorize::where('student_idno', $user->idno)->where('client_id', '*')->first();
        if ($authorize) return $next($request);
        $authorize = PSAuthorize::where('student_idno', $user->idno)->where('client_id', $client_id)->first();
        if ($authorize) {
            $trust = true;
            switch($authorize->trust_level) {
                case 0:
                    foreach ($scopes as $scope) {
                        if ($scope != 'school') $trust = false;
                    }
                    break;
                case 1:
                    foreach ($scopes as $scope) {
                        if (!in_array($scope, ['school', 'me', 'email', 'user'])) $trust = false;
                    }
                    break;
                case 2:
                    foreach ($scopes as $scope) {
                        if (!in_array($scope, ['school', 'me', 'email', 'user', 'idno', 'profile'])) $trust = false;
                    }
                    break;
                case 3:
                    $trust = true;
                    break;    
            }
            if ($trust) return $next($request);
        }

        $clients = Passport::client();
        $client = $clients->where($clients->getKeyName(), $client_id)->first();
        $client_name = $client->name;
        return view('auth.under13', [ 'client' => $client_name ]);
    }
}