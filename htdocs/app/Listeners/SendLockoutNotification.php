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
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($idno);
		$data = $openldap->getUserData($entry, 'mail');
		if (array_key_exists('mail', $data)) {
			if (is_array($data['mail'])) {
                foreach ($data['mail'] as $mail) {
                    Notification::route('mail', $mail)->notify(new LockoutNotification());
                }
			} else {
                $mail = $data['mail'];
                Notification::route('mail', $mail)->notify(new LockoutNotification());
            }
        }
        $users = User::where('is_admin', 1);
        if ($users) {
            foreach ($users as $u) {
                $u->notify(new LockoutAdminNotification($throttled));
            }
        }
    }
}
