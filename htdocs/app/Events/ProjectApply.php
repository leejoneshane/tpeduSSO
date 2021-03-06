<?php

namespace App\Events;

use App\Project;
use Illuminate\Queue\SerializesModels;

class ProjectApply
{
    use SerializesModels;

    public $project;

    /**
     * Create a new event instance.
     *
     * @param \App\Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }
}
