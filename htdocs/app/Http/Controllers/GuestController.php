<?php

namespace App\Http\Controllers;

use Config;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Project;

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
        return view('3party.store');
	}

    public function edit(Request $request)
	{
		$id = $request->get('uuid');
		$project = Project::find($id);
		return view('3party.edit', [ 'project' => $project ]);		
	}

}