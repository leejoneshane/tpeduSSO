<?php

namespace App\Listeners;

use App\Events\ProjectApply;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendProjectApplyNotification
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

    public function handle(ProjectApply $event)
    {
        $project = $event->project;
        $users = User::where('is_admin', 1)->get();
        if ($users) {
            foreach ($users as $u) {
                $u->notify(new ProjectApplyNotification($project));
            }
        }
    }
}
