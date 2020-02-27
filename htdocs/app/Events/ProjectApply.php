<?php

namespace App\Events;

use App\Project;
use Illuminate\Queue\SerializesModels;

class ProjectAllowed
{
    use SerializesModels;

    public $project;

    /**
     * Create a new event instance.
     *
     * @param  \App\Project  $project
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }
}