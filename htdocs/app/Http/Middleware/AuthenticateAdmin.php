<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateAdmin
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest() || (!Auth::guard($guard)->user()->is_admin && Auth::guard($guard)->user()->id != 1)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect('/');
            }
        }

		$md = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];
		if(!empty($uri) && strpos($uri,'?')) $uri = substr($uri,0,strpos($uri,'?'));

		$id = $request->route('id');
		$dc = $request->route('dc');
		$cn = $request->route('cn');
		$uuid = $request->route('uuid');

		if(substr($uri,0,6) == '/sync/'){
			if($md == 'GET'){
				$u = [
					'/sync/ps/sync_school' => ['module' => '資料維護/全誼校務行政系統-同步學校', 'note' => '同步學校'],
					'/sync/js/sync_school' => ['module' => '資料維護/巨耀校務行政系統-同步學校', 'note' => '同步學校'],
					'/sync/fix/remove_fake' => ['module' => '資料維護/移除假身份人員', 'note' => '移除假身份人員'],
					'/sync/fix/remove_deleted' => ['module' => '資料維護/移除標記為已刪除人員', 'note' => '移除標記為已刪除人員'],
				];

				if(array_key_exists($uri, $u))
					\App\UsageLogger::add($u[$uri]['module'], $this->reqParam($request), $u[$uri]['note']);
			}else if($md == 'POST'){
				$u = [
					'/sync/ps/runtime_test' => ['module' => '資料維護/全誼校務行政系統資料連線測試中心', 'note' => '連線測試'],
					'/sync/ps/sync_class' => ['module' => '資料維護/全誼校務行政系統-同步班級', 'note' => '同步班級'],
					'/sync/ps/sync_subject' => ['module' => '資料維護/全誼校務行政系統-同步教學科目', 'note' => '同步教學科目'],
					'/sync/ps/sync_teacher' => ['module' => '資料維護/全誼校務行政系統-同步教師', 'note' => '同步教師'],
					'/sync/ps/sync_student' => ['module' => '資料維護/全誼校務行政系統-同步學生', 'note' => '同步學生'],
					'/sync/ps/auto' => ['module' => '資料維護/全誼校務行政系統自動同步', 'note' => '自動同步'],
					'/sync/js/runtime_test' => ['module' => '資料維護/巨耀校務行政系統資料連線測試中心', 'note' => '連線測試'],
					'/sync/js/sync_ou' => ['module' => '資料維護/巨耀校務行政系統-同步行政部門', 'note' => '同步行政部門'],
					'/sync/js/sync_class' => ['module' => '資料維護/巨耀校務行政系統-同步班級', 'note' => '同步班級'],
					'/sync/js/sync_subject' => ['module' => '資料維護/巨耀校務行政系統-同步教學科目', 'note' => '同步教學科目'],
					'/sync/js/sync_teacher' => ['module' => '資料維護/巨耀校務行政系統-同步教師', 'note' => '同步教師'],
					'/sync/js/sync_student' => ['module' => '資料維護/巨耀校務行政系統-同步學生', 'note' => '同步學生'],
					'/sync/js/auto' => ['module' => '資料維護/巨耀校務行政系統自動同步', 'note' => '自動同步'],
				];

				if(array_key_exists($uri, $u))
					\App\UsageLogger::add($u[$uri]['module'], $this->reqParam($request), $u[$uri]['note']);
			}
		}else if(substr($uri,0,8) == '/bureau/'){
			if($md == 'GET'){
				
			}else if($md == 'POST'){
				$u = [
					'/bureau/thirdapp' => ['module' => '局端管理/第三方應用管理', 'note' => '新增資料'],
					'/bureau/admin/new' => ['module' => '局端管理/設定管理員', 'note' => '新增管理員'],
					'/bureau/admin/remove' => ['module' => '局端管理/設定管理員', 'note' => '刪除管理員'],
					'/bureau/organization/new' => ['module' => '局端管理/教育機構管理-線上編輯', 'note' => '新增資料'],
					'/bureau/organization/json' => ['module' => '局端管理/教育機構管理-匯入JSON', 'note' => '匯入JSON'],
					'/bureau/group' => ['module' => '局端管理/動態群組管理-新增群組', 'note' => '新增資料'],
					'/bureau/people/new' => ['module' => '局端管理/人員管理-新增人員', 'note' => '新增資料'],
					'/bureau/people/json' => ['module' => '局端管理/人員管理-匯入JSON', 'note' => '匯入JSON'],
					'/bureau/OauthScopeAccessLog' => ['module' => '局端管理/使用者授權同意日誌查詢', 'note' => '資料查詢'],
					'/bureau/usagerecord' => ['module' => '局端管理/系統作業日誌查詢', 'note' => '資料查詢'],
				];

				$p = $request->except(['_token']);

				if($id){
					$p['id'] = $id;
					$j = json_encode($p,JSON_UNESCAPED_UNICODE);
					$u['/bureau/thirdapp/'.$id.'/update'] = ['module' => '局端管理/第三方應用管理', 'content' => $j, 'note' => '修改資料'];
					$u['/bureau/thirdapp/'.$id.'/remove'] = ['module' => '局端管理/第三方應用管理', 'content' => $j, 'note' => '刪除資料'];
				}

				if($dc){
					$p['dc'] = $dc;
					$j = json_encode($p,JSON_UNESCAPED_UNICODE);
					$u['/bureau/organization/'.$dc.'/update'] = ['module' => '局端管理/教育機構管理-更新機構', 'content' => $j, 'note' => '修改資料'];
					$u['/bureau/organization/'.$dc.'/remove'] = ['module' => '局端管理/教育機構管理-刪除機構', 'content' => $j, 'note' => '刪除資料'];
				}

				if($cn){
					$p['cn'] = $cn;
					$j = json_encode($p,JSON_UNESCAPED_UNICODE);
					$u['/bureau/group/'.$cn.'/update'] = ['module' => '局端管理/動態群組管理-更新群組', 'content' => $j, 'note' => '修改資料'];
					$u['/bureau/group/'.$cn.'/remove'] = ['module' => '局端管理/動態群組管理-刪除群組', 'content' => $j, 'note' => '刪除資料'];
				}

				if($uuid){
					$p['uuid'] = $uuid;
					$j = json_encode($p,JSON_UNESCAPED_UNICODE);
					$u['/bureau/teacher/'.$uuid.'/update'] = ['module' => '局端管理/人員管理-更新教師', 'content' => $j, 'note' => '修改資料'];
					$u['/bureau/student/'.$uuid.'/update'] = ['module' => '局端管理/人員管理-更新學生', 'content' => $j, 'note' => '修改資料'];
					$u['/bureau/people/'.$uuid.'/remove'] = ['module' => '局端管理/人員管理-標記刪除', 'content' => $j, 'note' => '標記刪除'];
					$u['/bureau/people/'.$uuid.'/toggle'] = ['module' => '局端管理/人員管理-切換人員狀態', 'content' => $j, 'note' => '切換人員狀態'];
					$u['/bureau/people/'.$uuid.'/undo'] = ['module' => '局端管理/人員管理-取消刪除標記', 'content' => $j, 'note' => '取消刪除標記'];
					$u['/bureau/people/'.$uuid.'/resetpass'] = ['module' => '局端管理/人員管理-回復密碼', 'content' => $j, 'note' => '回復密碼'];
				}

				if(array_key_exists($uri, $u))
					\App\UsageLogger::add($u[$uri]['module'], array_key_exists('content',$u[$uri])?$u[$uri]['content']:$this->reqParam($request), $u[$uri]['note']);
/*
	Route::get('orgs/{area}', 'Api\schoolController@listOrgs');
	Route::get('units/{dc}', 'Api\schoolController@allOu');
	Route::get('roles/{dc}/{ou_id}', 'Api\schoolController@allRole');
	Route::get('classes/{dc}', 'Api\schoolController@listClasses');
*/
			}
		}

        return $next($request);
    }

	protected function reqParam($request)
	{
		return json_encode($request->except(['_token']),JSON_UNESCAPED_UNICODE);
	}
}