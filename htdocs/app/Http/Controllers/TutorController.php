<?php

namespace App\Http\Controllers;

use Auth;
use Log;
use Config;
use Carbon\Carbon;
use App\PSLink;
use App\GQrcode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Providers\LdapServiceProvider;
use App\Rules\idno;

class TutorController extends Controller
{

	public function index()
	{
		$user = Auth::user();
		$orgs = $user->ldap['o'];
		if (is_array($orgs))
			$dc = $orgs[0];
		else
			$dc = $orgs;
		$class = $user->ldap['tpTutorClass'];
		return view('tutor', [ 'dc' => $dc, 'ou' => $class ]);
	}

	public function classStudentForm(Request $request, $dc, $ou)
    {
		$openldap = new LdapServiceProvider();
		$filter = "(&(o=$dc)(tpClass=$ou)(employeeType=學生)(!(inetUserStatus=deleted)))";
		$students = $openldap->findUsers($filter, ["cn", "displayName", "o", "tpClass", "tpSeat", "entryUUID", "uid", "inetUserStatus"]);
		usort($students, function ($a, $b) { return $a['tpSeat'] <=> $b['tpSeat']; });
		return view('admin.classstudent', [ 'dc' => $dc, 'ou' => $ou, 'students' => $students ]);
	}

	public function studentEditForm(Request $request, $dc, $ou, $uuid)
	{
		$openldap = new LdapServiceProvider();
		$entry = $openldap->getUserEntry($uuid);
		$user = $openldap->getUserData($entry);
		return view('admin.classstudentedit', [ 'dc' => $dc, 'ou' => $ou, 'user' => $user ]);
	}
	
    public function updateStudent(Request $request, $dc, $ou, $uuid)
	{
		$openldap = new LdapServiceProvider();
		$validatedData = $request->validate([
			'idno' => new idno,
			'sn' => 'required|string',
			'gn' => 'required|string',
			'stdno' => 'required|string',
			'seat' => 'required|integer',
			'raddress' => 'nullable|string',
			'address' => 'nullable|string',
			'www' => 'nullable|url',
		]);
		$idno = strtoupper($request->get('idno'));
		$info = array();
		$info['employeeNumber'] = $request->get('stdno');
		$info['tpSeat'] = $request->get('seat');
		$info['sn'] = $request->get('sn');
		$info['givenName'] = $request->get('gn');
		$info['displayName'] = $info['sn'].$info['givenName'];
		$info['gender'] = (int) $request->get('gender');
		$info['birthDate'] = str_replace('-', '', $request->get('birth')).'000000Z';
		if (empty($request->get('raddress'))) 
			$info['registeredAddress'] = [];
		else
			$info['registeredAddress'] = $request->get('raddress');
		if (empty($request->get('address')))
			$info['homePostalAddress'] = [];
		else
			$info['homePostalAddress'] = $request->get('address');
		if (empty($request->get('www')))
			$info['wWWHomePage'] = [];
		else
			$info['wWWHomePage'] = $request->get('www');
		if (empty($request->get('character'))) {
				$info['tpCharacter'] = [];
		} else {
				$data = array();
				if (is_array($request->get('character'))) {
					foreach ($request->get('character') as $character) {
						if ($character != '縣市管理者') $data[] = $character;
					}
				} elseif ($request->get('character') != '縣市管理者') {
					$data[] = $request->get('character');
				}
				$data = array_values(array_filter($data));
				if (!empty($data)) $info['tpCharacter'] = $data;
		}
		if (empty($request->get('mail'))) {
			$info['mail'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mail'))) {
	    		$data = $request->get('mail');
			} else {
	    		$data[] = $request->get('mail');
			}
			$info['mail'] = array_values(array_filter($data));
		}
		if (empty($request->get('mobile'))) {
			$info['mobile'] = [];
		} else {
			$data = array();
			if (is_array($request->get('mobile'))) {
	    		$data = $request->get('mobile');
			} else {
	    		$data[] = $request->get('mobile');
			}
			$info['mobile'] = array_values(array_filter($data));
		}
		if (empty($request->get('otel'))) {
			$info['telephoneNumber'] = [];
		} else {
			$data = array();
			if (is_array($request->get('otel'))) {
	    		$data = $request->get('otel');
			} else {
	    		$data[] = $request->get('otel');
			}
			$info['telephoneNumber'] = array_values(array_filter($data));
		}
		if (empty($request->get('htel'))) {
			$info['homePhone'] = [];
		} else {
			$data = array();
			if (is_array($request->get('htel'))) {
	    		$data = $request->get('htel');
			} else {
	    		$data[] = $request->get('htel');
			}
			$info['homePhone'] = array_values(array_filter($data));
		}
				
		$entry = $openldap->getUserEntry($uuid);
		$result = $openldap->updateData($entry, $info);
		if ($result) {
			return back()->with("success", "已經為您更新學生基本資料！");
		} else {
			return back()->with("error", "學生基本資料變更失敗！".$openldap->error());
		}
	}

	public function classLinkForm(Request $request, $dc, $ou)
	{
		$openldap = new LdapServiceProvider();
		$links = PSLink::byClass($dc, $ou);
		$records = array();
		if ($links) {
			foreach ($links as $l) {
				$link_id = $l->id;
				$parent = $l->parent();
				$student_idno = $l->student_idno;
				$entry = $openldap->getUserEntry($student_idno);
				$data = $openldap->getUserData($entry);
				$k = array();
				$k['parent'] = $parent->name;
				$k['email'] = $parent->email;
				$k['mobile'] = $parent->mobile;
				$k['student'] = $data['displayName'];
				$k['seat'] = $data['tpSeat'];
				$records[$link_id] = $k;
			}	
		}
		return view('admin.classListLink', [ 'dc' => $dc, 'ou' => $ou, 'links' => $links, 'records' => $records ]);
	}

	public function classQrcodeForm(Request $request, $dc, $ou)
    {
		$openldap = new LdapServiceProvider();
		$filter = "(&(o=$dc)(tpClass=$ou)(employeeType=學生)(!(inetUserStatus=deleted)))";
		$students = $openldap->findUsers($filter, ["cn", "displayName", "o", "tpClass", "tpSeat", "entryUUID", "uid", "inetUserStatus"]);
		usort($students, function ($a, $b) { return $a['tpSeat'] <=> $b['tpSeat']; });
		$cnt = 0;
		foreach ($students as $k => $st) {
			$qrcode = GQrcode::where('idno', $st['cn'])->first();
			if ($qrcode) {
				$students[$k]['QRCODE'] = $qrcode->generate();
				$students[$k]['expired'] = $qrcode->expired_at;
				$cnt ++;
			}
		}
		if ($cnt == 0) {
			foreach ($students as $k => $st) {
				GQrcode::create([
					'idno' => $st['cn'],
					'expired_at' => Carbon::today()->addDays(Config::get('app.QRCodeExpireDays')),
				]);
				$qrcode = GQrcode::where('idno', $st->idno)->first();
				$students[$k]['QRCODE'] = $qrcode->generate();
				$students[$k]['expired'] = $qrcode->expired_at;
			}
		}
		return view('admin.classstudentqrcode', [ 'dc' => $dc, 'ou' => $ou, 'students' => $students ]);
	}

}