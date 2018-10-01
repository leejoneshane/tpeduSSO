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
				if ($my_field && $request->has('sid0')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid0') ]);
				}
				break;
			case 1:
				if ($my_field && $request->has('sid1') && $request->has('grade')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid1'), '{grade}' => $request->get('grade') ]);
				}
				break;
			case 2:
				if ($my_field && $request->has('sid2') && $request->has('subjid')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid2'), '{subjid}' => $request->get('subjid') ]);
				}
				break;
			case 3:
				if ($my_field && $request->has('sid3') && $request->has('clsid')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid3'), '{clsid}' => $request->get('clsid') ]);
				}
				break;
			case 4:
				if ($my_field && $request->has('sid4') && $request->has('teaid')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid4'), '{teaid}' => $request->get('teaid') ]);
				}
				break;
			case 5:
				if ($my_field && $request->has('sid5') && $request->has('stdno')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid5'), '{stdno}' => $request->get('stdno') ]);
				}
				break;
			case 6:
				if ($my_field && $request->has('sid6') && $request->has('isbn')) {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid6'), '{isbn}' => $request->get('isbn') ]);
				} else {
					$result = $http->ps_call($my_field, [ '{sid}' => $request->get('sid6') ]);
				}
				break;
		}
		return view('admin.synctest', [ 'my_field' => $my_field, 'result' => $result ]);
    }

}
