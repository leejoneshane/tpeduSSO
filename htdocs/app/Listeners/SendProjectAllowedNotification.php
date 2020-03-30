<?php

namespace App\Listeners;

use App\Events\ProjectAllowed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ProjectAllowedNotification;

class SendProjectAllowedNotification
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

    public function handle(ProjectAllowed $event)
    {
        $project = $event->project;
        if (!empty($this->connEmail)) {
            $project->notify(new ProjectAllowedNotification($project));
        }
    }
}
