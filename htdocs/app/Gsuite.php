<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gsuite extends Model
{

	protected $table = 'gsuite';

	protected $primaryKey = 'idno';

    protected $fillable = [
        'idno', 'nameID', 'primary', 'transfered',
    ];
    
    protected $casts = [
		'primary' => 'boolean',
		'transfered' => 'boolean',
    ];

	public function user()
	{
    	return $this->belongsTo('App\User', 'idno', 'idno');
	}
}
