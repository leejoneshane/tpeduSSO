<?php

namespace App;

use QrCode;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class GQrcode extends Model
{

	protected $table = 'guardian_qrcode';

	public $timestamps = false;

	protected $primaryKey = 'uuid';

	protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid', 'idno', 'expired_at',
    ];
    
    protected $casts = [
		'expired_at' => 'datetime',
    ];

	protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), (string) Str::uuid());
        });
    }

	public function user()
	{
    	return $this->belongsTo('App\User', 'idno', 'idno');
	}

	public function generate()
	{
		return QrCode::size(100)->generate(config('app.url').'/parent/qrcode/'.$this->attributes['uuid']);
	}

	public function expired()
	{
    	return Carbon::today() > new Carbon($this->attributes['expired_at']);
	}

}
