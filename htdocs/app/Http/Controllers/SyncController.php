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
		$scope = $request->get('scope');
		$my_field = $request->get('field');
		$http = new SimsServiceProvider();
		$result = array();
		if ($my_field && $request->has('sid')) {
			$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid') ]);
		}
		if ($my_field && $request->has('sid') && $request->has('grade')) {
			$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{grade}' => $request->get('grade') ]);
		}
		if ($my_field && $request->has('sid') && $request->has('subjid')) {
			$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{subjid}' => $request->get('subjid') ]);
		}
		if ($my_field && $request->has('sid') && $request->has('clsid')) {
			$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{clsid}' => $request->get('clsid') ]);
		}
		if ($my_field && $request->has('sid') && $request->has('teaid')) {
			$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{teaid}' => $request->get('teaid') ]);
		}
		if ($my_field && $request->has('sid') && $request->has('stdno')) {
			$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{stdno}' => $request->get('stdno') ]);
		}
		if ($my_field && $request->has('sid') && $request->has('isbn')) {
			$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{isbn}' => $request->get('isbn') ]);
		}
		return view('admin.synctest', [ 'my_field' => $my_field, 'result' => $result ]);
    }

}
