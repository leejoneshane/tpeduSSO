<?php

namespace App\Http\Controllers;

use Config;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Project;
use App\GQrcode;
use App\Events\ProjectApply;
use App\Providers\LdapServiceProvider;

class GuestController extends Controller
{

	public function apply()
    {
        return view('3party.apply');
    }

    public function store(Request $request)
	{
		$validatedData = $request->validate([
            'organization' => 'required|string|max:150',
            'applicationName' => 'required|string|max:150',
            'reason' => 'required|string|max:255',
            'website' => 'required|url',
            'redirect' => 'required|url',
            'connName' => 'required|string|max:50',
            'connUnit' => 'sometimes|string|max:150',
            'connEmail' => 'required|email:rfc,dns',
            'connTel' => 'required|digits_between:7,10',
		]);
		if ($request->get('id')) {
			$project = Project::find($id);
			$project->forceFill([
				'organization' => $request->get('organization'),
				'applicationName' => $request->get('applicationName'),
				'reason' => $request->get('reason'),
				'website' => $request->get('website'),
				'redirect' => $request->get('redirect'),
				'kind' => $request->get('kind'),
				'connName' => $request->get('connName'),
				'connUnit' => $request->get('connUnit') ?: '',
				'connEmail' => $request->get('connEmail') ?: '',
				'connTel' => $request->get('connTel'),
			])->save();
			$client = $project->client();
			if ($client) {
				$client->forceFill([
					'name' => $request->get('applicationName'),
					'redirect' => $request->get('redirect'),
				])->save();	
			}
		} else {
			Project::create([
				'id' => (string) Str::uuid(),
				'organization' => $request->get('organization'),
				'applicationName' => $request->get('applicationName'),
				'reason' => $request->get('reason'),
				'website' => $request->get('website'),
				'redirect' => $request->get('redirect'),
				'kind' => $request->get('kind'),
				'connName' => $request->get('connName'),
				'connUnit' => $request->get('connUnit') ?: '',
				'connEmail' => $request->get('connEmail') ?: '',
				'connTel' => $request->get('connTel'),
			]);	
		}
		event(new ProjectApply($project));
        return view('3party.store');
	}

    public function edit(Request $request)
	{
		$id = $request->get('uuid');
		$project = Project::find($id);
		return view('3party.edit', [ 'project' => $project ]);		
	}

	public function showGuardianAuthForm(Request $request, $id)
    {
		$openldap = new LdapServiceProvider();
		$qrcode = GQrcode::find($id);
		if ($qrcode && $qrcode->expired()) return redirect()->route('home');
		$student = $qrcode->idno;
		$kids = array();
		$entry = $openldap->getUserEntry($student);
		$data = $openldap->getUserData($entry);
		$age = Carbon::today()->subYears(13);
		$str = $data['birthDate'];
		$born = Carbon::createFromDate(substr($str,0,4), substr($str,4,2), substr($str,6,2), 'Asia/Taipei');
		if ($born > $age) {
			$kids[$student] = $data['displayName'];
		}
		$apps = Passport::client()->all();
		foreach ($apps as $k => $app) {
			if ($app->firstParty()) unset($apps[$k]);
		}
		$agreeAll = null;
		$agreeAll = PSAuthorize::where('student_idno', $student)->where('client_id', '*')->first();
		$data = PSAuthorize::where('student_idno', $student)->where('client_id', '!=', '*')->get();
		$authorizes = array();
		foreach ($data as $d) {
			$authorizes[$d->client_id] = $d->trust_level;
		}
		return view('parents.guardianAuthForm', [ 'student' => $student, 'kids' => $kids, 'apps' => $apps, 'agreeAll' => $agreeAll, 'authorizes' => $authorizes, 'trust_level' => Config::get('app.trust_level') ]);		
	}

	public function applyGuardianAuth(Request $request, $id)
    {
		$parent_idno = 'qrcode';
		$qrcode = GQrcode::find($id);
		if ($qrcode && $qrcode->expired()) return redirect()->route('home');
		$student = $qrcode->idno;
		$agreeAll = $request->get('agreeAll');
		$agree = $request->get('agree');
		if (!empty($agreeAll)) {
			if ($agreeAll == 'new') {
				PSAuthorize::create([
					'parent_idno' => $parent_idno,
					'student_idno' => $student,
					'client_id' => '*',
					'trust_level' => 3,
				]);
			} else {
				PSAuthorize::where('student_idno', $student)->where('client_id', '!=', '*')->delete();
			}
		} else {
			$apps = Passport::client()->all();
			foreach ($apps as $app) {
				if ($app->firstParty()) continue;
				if (in_array($app->id, $agree)) {
					$trust_level = $request->get($app->id.'level');
					$old = PSAuthorize::where('student_idno', $student)->where('client_id', $app->id)->first();
					if ($old) {
						$old->trust_level = $trust_level;
						$old->save();
					} else {
						PSAuthorize::create([
							'parent_idno' => $parent_idno,
							'student_idno' => $student,
							'client_id' => $app->id,
							'trust_level' => $trust_level,
						]);
					}
				} else {
					PSAuthorize::where('student_idno', $student)->where('client_id', $app->id)->delete();
				}
			}
		}
		return redirect()->route('qrcode', [ 'id' => $id ])->with("success","已經為您更新代理授權設定！");
	}

}