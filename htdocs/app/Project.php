<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    protected $table = 'projects';

    protected $fillable = [
        'organizaton', 'applicationName', 'reason', 'website', 'redirect', 'kind', 'connName', 'connUnit', 'connEmail', 'connTel', 'memo', 'audit', 'clients',
    ];
    
    protected $casts = [
		'audit' => 'boolean',
    ];

}
