<?php

namespace App\Listeners;

use Notification;
use App\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Providers\LdapServiceProvider;
use App\Notifications\LockoutNotification;

class SendLockoutNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Lockout  $event
     * @return void
     */
    public function handle(Lockout $event)
    {
        $throttled = $event->request;
        $idno = $throttled->get('idno');
        $trylogin = User::where('idno', $idno)->first();
        if ($trylogin && $trylogin->hasVerifiedEmail()) {
            $trylogin->notify(new LockoutNotification());
        }
        $users = User::where('is_admin', 1);
        if ($users) {
            foreach ($users as $u) {
                $u->notify(new LockoutAdminNotification($throttled));
            }
        }
    }
}
