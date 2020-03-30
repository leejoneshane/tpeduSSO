<?php

namespace App\Listeners;

use App\Events\ClientChange;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ClientChangeNotification;

class SendClientChangeNotification
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

    public function handle(ClientChange $event)
    {
        $project = $event->project;
        if (!empty($this->connEmail)) {
            $project->notify(new ClientChangeNotification($project));
        }
    }
}
