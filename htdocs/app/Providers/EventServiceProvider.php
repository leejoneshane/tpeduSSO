<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Lockout;
use App\Events\ProjectAllowed;
use App\Events\ClientChange;
use App\Events\ProjectApply;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
//        Lockout::class => [
//            'App\Listeners\SendLockoutNotification',
//        ],
        'Laravel\Passport\Events\AccessTokenCreated' => [
            'App\Listeners\RevokeOldTokens',
        ],
        'Laravel\Passport\Events\RefreshTokenCreated' => [
            'App\Listeners\PruneOldTokens',
        ],
        SocialiteWasCalled::class => [
            'SocialiteProviders\Yahoo\YahooExtendSocialite@handle',
            'SocialiteProviders\Line\LineExtendSocialite@handle',
        ],
        ProjectAllowed::class => [
            'App\Listeners\SendProjectAllowedNotification',
        ],
        ClientChange::class => [
            'App\Listeners\SendClientChangeNotification',
        ],
        ProjectApply::class => [
            'App\Listeners\SendProjectApplyNotification',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
