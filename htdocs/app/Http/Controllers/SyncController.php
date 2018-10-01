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
		switch ($scope) {
			case 0:
				if ($my_field && $request->has('sid')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid') ]);
				}
				break;
			case 1:
				if ($my_field && $request->has('sid') && $request->has('grade')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{grade}' => $request->get('grade') ]);
				}
				break;
			case 2:
				if ($my_field && $request->has('sid') && $request->has('subjid')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{subjid}' => $request->get('subjid') ]);
				}
				break;
			case 3:
				if ($my_field && $request->has('sid') && $request->has('clsid')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{clsid}' => $request->get('clsid') ]);
				}
				break;
			case 4:
				if ($my_field && $request->has('sid') && $request->has('teaid')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{teaid}' => $request->get('teaid') ]);
				}
				break;
			case 5:
				if ($my_field && $request->has('sid') && $request->has('stdno')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{stdno}' => $request->get('stdno') ]);
				}
				break;
			case 6:
				if ($my_field && $request->has('sid') && $request->has('isbn')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid'), '{isbn}' => $request->get('isbn') ]);
				} else {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid') ]);
				}
				break;
		}
		return view('admin.synctest', [ 'my_field' => $my_field, 'sid' => $sid, 'result' => $result ]);
    }

}
