<?php

namespace App;

use QrCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GQrcode extends Model
{

	protected $table = 'guardian_qrcode';

	public $timestamps = false;

    protected $fillable = [
        'uuid', 'idno', 'expired_at',
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
		return QrCode::size(100)->generate(env('APP_URL').'/qrcode/'.$this->attributes['uuid']);
	}

	public function expired()
	{
    	return Carbon::today() > new Carbon($this->attributes['expired_at']);
	}

}
