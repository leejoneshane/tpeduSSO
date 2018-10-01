<?php

namespace App\Http\Controllers;

use Config;
use Validator;
use Auth;
use Illuminate\Http\Request;
use App\Providers\SimsServiceProvider;
use App\Rules\idno;
use App\Rules\ipv4cidr;
use App\Rules\ipv6cidr;

class SyncController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sync');
    }
    
    public function ps_testForm(Request $request)
    {
		$my_field = $request->get('field');
		$sid = $request->get('sid');
		$http = new SimsServiceProvider();
		$result = array();
		if ($my_field && $sid) {
			$result = $http->ps_call($my_field, [ '{sid}' => $sid ]);
		}
		return view('admin.synctest', [ 'my_field' => $my_field, 'sid' => $sid, 'result' => $result ]);
    }

}
