<?php

namespace App\Providers;

use Auth;
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
        Passport::tokensExpireIn(Carbon::now()->addDay());
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
        Passport::tokensCan([
    	    'user' => '取得和修改目前登入者的識別代號、姓名、電子郵件、手機號碼等資訊',
    	    'idno' => '取得目前登入者的身分證字號',
    	    'profile' => '取得目前登入者的身份、所屬機構、單位職稱、就讀年班等資訊',
	        'account' => '修改目前登入者的自訂帳號和密碼',
	        'school' => '讀取學校公開資訊',
    	    'schoolAdmin' => '更新學校資訊，增刪修學校人員',
    	]);

        Auth::provider('ldap', function($app, array $config) {
    	    return new LdapUserProvider($app['hash'], $config['model']);
    	});
    }
}
