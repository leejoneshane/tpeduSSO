<?php

namespace App\Listeners;

use DB;
use Laravel\Passport\Events\AccessTokenCreated;

class RevokeOldTokens
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param AccessTokenCreated $event
     */
    public function handle(AccessTokenCreated $event)
    {
        try {
            DB::table('oauth_access_tokens')
                ->where('id', '<>', $event->tokenId)
                ->where('user_id', $event->userId)
                ->where('client_id', $event->clientId)
                ->update(['revoked' => true]);
            DB::table('oauth_auth_codes')
                ->where('user_id', $event->userId)
                ->where('client_id', $event->clientId)
                ->update(['revoked' => true]);
        } catch (\Exception $e) {
        }
    }
}
