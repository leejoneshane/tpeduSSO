<?php

namespace App;

use Auth;
use App\Usagerecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsageLogger {

    public static function add($module, $content, $note) {
		$log = new Usagerecord;
		$log->userid = Auth::user()->uuid;
		$log->username = Auth::user()->name;
		$log->ipaddress = $_SERVER['REMOTE_ADDR'];
		$log->eventtime = date("YmdHis");
		$log->module = $module;
		$log->content = $content;
		$log->note = $note;
		$log->save();
    }

    public static function add2($userid, $username, $module, $content, $note) {
		$log = new Usagerecord;
		$log->userid = $userid;
		$log->username = $username;
		$log->ipaddress = $_SERVER['REMOTE_ADDR'];
		$log->eventtime = date("YmdHis");
		$log->module = $module;
		$log->content = $content;
		$log->note = $note;
		$log->save();
    }

    public static function add3($userid, $username, $time, $module, $content, $note) {
		$log = new Usagerecord;
		$log->userid = $userid;
		$log->username = $username;
		$log->ipaddress = $_SERVER['REMOTE_ADDR'];
		$log->eventtime = $time;
		$log->module = $module;
		$log->content = $content;
		$log->note = $note;
		$log->save();
    }
}
?>