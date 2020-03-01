<?php

namespace App;

use Carbon;
use Qrcode;
use Illuminate\Database\Eloquent\Model;

class Gsuite extends Model
{

	protected $table = 'guardian_qrcode';

    protected $fillable = [
        'id', 'idno', 'expired_at',
    ];
    
    protected $casts = [
		'expired_at' => 'datetime',
    ];

	public function user()
	{
    	return $this->belongsTo('App\User', 'idno', 'idno');
	}

	public function generate()
	{
		return QrCode::generate(env('APP_URL').'/qrcode/'.$this->id);
	}

	public function expired()
	{
    	return Carbon::today() > new Carbon($this->attributes['expired_at']);
	}

}
