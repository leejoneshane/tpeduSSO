<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OauthScopeAccessLog extends Model
{
    protected $table = 'oauth_scope_access_log';
    public $timestamps = true;
    
    public static function add($system_id, $authorizer, $approve, $scope, $scope_range) {
		$log = new OauthScopeAccessLog;
		$log->system_id = $system_id;
		$log->authorizer = $authorizer;
		$log->approve = $approve;
		$log->scope = $scope;
		$log->scope_range = $scope_range;
		$log->save();
    }
}