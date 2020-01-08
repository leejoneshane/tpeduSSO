<?php

namespace App\Providers;

use Auth;
use Route;
use Carbon\Carbon;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Providers\LdapUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
        Passport::routes();
        Passport::tokensExpireIn(Carbon::now()->addHours(2));
        Passport::refreshTokensExpireIn(Carbon::now()->addDay());
//        Passport::personalAccessTokensExpireIn(Carbon::now()->addMonths(6));
        Passport::tokensCan([
    	    'me' => '想要取得您的電子郵件和姓名',
    	    'email' => '想要取得您的電子郵件',
    	    'user' => '想要取得您的識別代號、姓名、電子郵件、手機號碼等資訊',
    	    'idno' => '想要取得您的身分證字號',
    	    'profile' => '想要取得您的身份、所屬機構、單位職稱、任教班級、任教科目、就讀年班等資訊',
	        'account' => '想要修改您的自訂帳號、電子郵件、手機號碼和密碼',
	        'school' => '想要讀取學校公開資訊',
    	    'schoolAdmin' => '想要更新學校資訊，以及增刪修學校人員',
    	    'admin' => '想要更新所有學校資訊，以及增刪修所有學校人員',
    	]);

        Auth::provider('ldap', function($app, array $config) {
    	    return new LdapUserProvider($app['hash'], $config['model']);
    	});
    }
}
