<?php

namespace App;

use Auth;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Passport;

class Project extends Model
{
    use Notifiable;

    protected $table = 'projects';

    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid', 'organizaton', 'applicationName', 'reason', 'website', 'redirect', 'kind', 'connName', 'connUnit', 'connEmail', 'connTel', 'memo', 'audit', 'client', 'privileged',
    ];
    
    protected $casts = [
        'audit' => 'boolean',
        'privileged' => 'boolean',
    ];

    public function routeNotificationForMail($notification)
    {
        return $this->attributes['connEmail'] ?: false;
    }

    public function sendmail(array $messages, $header = '')
    {
        if (empty($this->connEmail)) return false;
        $this->notify(new ProjectNotification($this, $messages, $header));
    }

    public function client() //取得 OAuth 用戶端
    {
        if (empty($this->client)) return false;
        return Passport::client()->where('id', $this->attributes['client'])->first();
    }

    public function findClient() //搜尋已存在的 OAuth 用戶端
    {
        return Passport::client()->where('name', $this->attributes['applicationName'])->where('redirect', $this->attributes['redirect'])->first();
    }

    public function buildClient() //建立 OAuth 用戶端
    {
        if (empty($this->attributes['client'])) {
            $client = $this->findClient();
            if ($client) { //連結已存在的用戶端
                $this->attributes['client'] = $client->id;
                $this->keep()->save();
            } else {
                $client = Passport::client()->forceFill([
                    'user_id' => Auth::user()->getKey(),
                    'name' => $this->attributes['applicationName'],
                    'secret' => Str::random(40),
                    'redirect' => $this->attributes['redirect'],
                    'personal_access_client' => 0,
                    'password_client' => 0,
                    'revoked' => false,
                ])->save();
                $this->attributes['client'] = $client->id;
                $this->save();
            }
        }
        return $this;
    }

    public static function privileged() //取得所有特權專案
    {
        return Project::where('privileged', 1)->get();
    }

    public static function byClient($client_id)
    {
        return Project::where('client', $client_id)->first();
    }

    public static function isPrivileged($client_id) //檢查 client 是否為特權專案
    {
        $project = Project::byClient($client_id);
        if ($project && $project->attributes['privileged']) return true;
        return false;
    }

    public function reject() //拒絕申請
    {
        $this->attributes['audit'] = false;
        $this->revoke()->save();
        return $this;
    }

    public function allow() //核准申請
    {
        $this->buildClient();
        $this->attributes['audit'] = true;
        $this->save();
        return $this;
    }

    public function revoke() //廢止用戶端
    {
        $client = $this->client();
        if ($client) $this->forceFill(['revoked' => true])->save();
        return $this;
    }

    public function keep() //回復用戶端
    {
        $client = $this->client();
        if ($client) $this->forceFill(['revoked' => false])->save();
        return $this;
    }

}
