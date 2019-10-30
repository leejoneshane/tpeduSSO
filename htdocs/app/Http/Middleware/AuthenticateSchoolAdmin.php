<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateSchoolAdmin
{
    public function handle($request, Closure $next, $guard = null)
    {
        $user = $request->user();
        $dc = $request->route('dc');
        if (Auth::guard($guard)->guest() || !is_array($user->ldap['adminSchools']) || !in_array($dc, $user->ldap['adminSchools'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/');
            }
        }
/*
		$md = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];
		if(!empty($uri) && strpos($uri,'?')) $uri = substr($uri,0,strpos($uri,'?'));

		$ou = $request->route('ou');
		$role = $request->route('role');
		$subject = $request->route('subject');
		$uuid = $request->route('uuid');

		if($md == 'POST'){
			$pf = '/school/'.$dc;

			$u = [
				'/schoolAdmin' => ['module' => '學校管理/設定管理員', 'note' => '新增管理員'],
				'/schoolAdminRemove' => ['module' => '學校管理/設定管理員', 'note' => '刪除管理員'],
				$pf.'/profile' => ['module' => '學校管理/學校基本資料', 'note' => '修改資料'],
				$pf.'/unit' => ['module' => '學校管理/行政部門管理-新增部門', 'note' => '新增資料'],
				$pf.'/sync_class' => ['module' => '學校管理/同步班級資訊', 'note' => '同步班級資訊'],
				$pf.'/class' => ['module' => '學校管理/班級管理-新增班級', 'note' => '新增資料'],
				$pf.'/sync_subject' => ['module' => '學校管理/同步教學科目', 'note' => '同步教學科目'],
				$pf.'/subject' => ['module' => '學校管理/教學科目管理-新增科目', 'note' => '新增資料'],
				$pf.'/class/assign' => ['module' => '學校管理/管理班級配課', 'note' => '維護資料'],
				$pf.'/sync_teacher' => ['module' => '學校管理/同步教師', 'note' => '同步教師'],
				$pf.'/teacher/new' => ['module' => '學校管理/教師管理-新增教師', 'note' => '新增資料'],
				$pf.'/teacher/json' => ['module' => '學校管理/教師管理-匯入JSON', 'note' => '匯入JSON'],
				$pf.'/sync_student' => ['module' => '學校管理/同步學生', 'note' => '同步學生'],
				$pf.'/student/new' => ['module' => '學校管理/學生管理-新增學生', 'note' => '新增資料'],
				$pf.'/student/json' => ['module' => '學校管理/學生管理-匯入JSON', 'note' => '匯入JSON'],
			];

			$p = $request->except(['_token']);

			if($ou){
				$p['dc'] = $dc;
				$p['ou'] = $ou;
				$j = json_encode($p,JSON_UNESCAPED_UNICODE);
				$u[$pf.'/unit/'.$ou.'/update'] = ['module' => '學校管理/行政部門管理-更新部門', 'content' => $j, 'note' => '修改資料'];
				$u[$pf.'/unit/'.$ou.'/remove'] = ['module' => '學校管理/行政部門管理-刪除部門', 'content' => $j, 'note' => '刪除資料'];
				$u[$pf.'/unit/'.$ou.'/role'] = ['module' => '學校管理/行政部門管理-新增部門', 'content' => $j, 'note' => '新增資料'];
				$u[$pf.'/class/'.$ou.'/update'] = ['module' => '學校管理/班級管理-更新班級', 'content' => $j, 'note' => '修改資料'];
				$u[$pf.'/class/'.$ou.'/remove'] = ['module' => '學校管理/班級管理-刪除班級', 'content' => $j, 'note' => '刪除資料'];

				if($role){
					$p['role'] = $role;
					$j = json_encode($p,JSON_UNESCAPED_UNICODE);
					$u[$pf.'/unit/'.$ou.'/role/'.$role.'/update'] = ['module' => '學校管理/職稱管理-更新職稱', 'content' => $j, 'note' => '修改資料'];
					$u[$pf.'/unit/'.$ou.'/role/'.$role.'/remove'] = ['module' => '學校管理/職稱管理-刪除職稱', 'content' => $j, 'note' => '刪除資料'];
				}
			}

			if($subject){
				$p['subject'] = $subject;
				$j = json_encode($p,JSON_UNESCAPED_UNICODE);
				$u[$pf.'/subject/'.$subject.'/update'] = ['module' => '學校管理/教學科目管理-更新科目', 'content' => $j, 'note' => '修改資料'];
				$u[$pf.'/subject/'.$subject.'/remove'] = ['module' => '學校管理/教學科目管理-刪除科目', 'content' => $j, 'note' => '刪除資料'];
			}

			if($uuid){
				$p['uuid'] = $uuid;
				$j = json_encode($p,JSON_UNESCAPED_UNICODE);
				$u[$pf.'/people/'.$uuid.'/toggle'] = ['module' => '學校管理/[教師/學生管理]-切換人員狀態', 'content' => $j, 'note' => '切換人員狀態'];
				$u[$pf.'/people/'.$uuid.'/remove'] = ['module' => '學校管理/[教師/學生管理]-標記刪除', 'content' => $j, 'note' => '標記刪除'];
				$u[$pf.'/people/'.$uuid.'/undo'] = ['module' => '學校管理/[教師/學生管理]-取消刪除標記', 'content' => $j, 'note' => '取消刪除標記'];
				$u[$pf.'/people/'.$uuid.'/resetpass'] = ['module' => '學校管理/[教師/學生管理]-回復密碼', 'content' => $j, 'note' => '回復密碼'];
				$u[$pf.'/teacher/'.$uuid.'/update'] = ['module' => '學校管理/教師管理-更新教師', 'content' => $j, 'note' => '修改資料'];
				$u[$pf.'/student/'.$uuid.'/update'] = ['module' => '學校管理/學生管理-更新學生', 'content' => $j, 'note' => '修改資料'];
			}

			if(array_key_exists($uri, $u))
				\App\UsageLogger::add($u[$uri]['module'], array_key_exists('content',$u[$uri])?$u[$uri]['content']:$this->reqParam($request), $u[$uri]['note']);
		}
        return $next($request);
    }

	protected function reqParam($request)
	{
		$p = $request->except(['_token']);
		$p['dc'] = $request->route('dc');
		return json_encode($p,JSON_UNESCAPED_UNICODE);*/
	}
}