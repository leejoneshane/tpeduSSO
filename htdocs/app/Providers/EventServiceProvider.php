<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        Lockout::class => [
            'App\Listeners\SendLockoutNotification',
        ],
        'Laravel\Passport\Events\AccessTokenCreated' => [
            'App\Listeners\RevokeOldTokens',
        ],
        'Laravel\Passport\Events\RefreshTokenCreated' => [
            'App\Listeners\PruneOldTokens',
        ],

        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            // add your listeners (aka providers) here
            'SocialiteProviders\\Facebook\\FacebookExtendSocialite@handle',
            'SocialiteProviders\\Yahoo\\YahooExtendSocialite@handle',
            'SocialiteProviders\\Google\\GoogleExtendSocialite@handle',
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
