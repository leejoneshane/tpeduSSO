<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gsuite extends Model
{

	protected $table = 'gsuite';

    protected $fillable = [
        'idno', 'gmail', 'primary',
    ];
    
    protected $casts = [
		'primary' => 'boolean',
    ];

	public function user()
	{
    	return $this->belongsTo('App\User', 'idno', 'idno');
	}
}
