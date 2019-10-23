<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);
/*
		$md = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];
		if(!empty($uri) && strpos($uri,'?')) $uri = substr($uri,0,strpos($uri,'?'));

		$uuid = $request->route('uuid');
		$client_id = $request->route('client_id');
		$token_id = $request->route('token_id');

		$u = [];

		if($client_id){
			if($uri == '/oauth/clients/'.$client_id){
				if($md == 'PUT'){
					$u['/oauth/clients/'.$client_id] = ['module' => '個人管理/金鑰管理-編輯專案', 'note' => '修改資料'];
				}else if($md == 'DELETE'){
					$u['/oauth/clients/'.$client_id] = ['module' => '個人管理/金鑰管理-刪除專案', 'note' => '刪除資料'];
				}
			}else if($uri == '/oauth/personal-access-tokens/'.$client_id && $md == 'DELETE'){
				$u['/oauth/personal-access-tokens/'.$client_id] = ['module' => '個人管理/個人存取金鑰-刪除金鑰', 'note' => '刪除資料'];
			}
		}

		if($token_id){
			if($uri == '/oauth/tokens/'.$token_id && $md == 'DELETE')
				$u['/oauth/tokens/'.$token_id] = ['module' => '個人管理/金鑰管理-刪除金鑰', 'note' => '刪除資料'];
		}

		if($md == 'POST'){
			$u['/oauth/personal-access-tokens'] = ['module' => '個人管理/個人存取金鑰-建立金鑰', 'note' => '新增資料'];
			$u['/oauth/clients'] = ['module' => '個人管理/金鑰管理-建立專案', 'note' => '新增資料'];
			$u['/profile'] = ['module' => '個人管理/修改個資', 'note' => '修改資料'];
			$u['/personal/listparentsqrcode'] = ['module' => '個人管理/導師班學生管理-列印家長QR-CODE', 'note' => '列印家長QR-CODE'];
			$u['/personal/parentsqrcode'] = ['module' => '個人管理/導師班學生管理-產生家長QR-CODE', 'note' => '產生家長QR-CODE'];
			$u['/personal/linkedChange'] = ['module' => '個人管理/導師班學生管理-變更親子連結狀態', 'note' => '變更親子連結狀態'];
			$u['/parents/connectChild'] = ['module' => '個人管理/親子連結-建立親子連結', 'note' => '建立親子連結'];
			$u['/parents/authConnectChild'] = ['module' => '個人管理/第三方應用授權-個資授權', 'note' => '個資授權'];

			$u['/personal/gsuiteregister'] = ['module' => '個人管理/使用G-Suite服務-註冊帳號', 'note' => '註冊G-Suite帳號'];
			$u['/personal/teacher_courses'] = ['module' => '個人管理/G-Suite Classroom 建立-建立課程', 'note' => '建立Classroom課程'];

			$p = $request->except(['_token']);

			if($uuid){
				$p['uuid'] = $uuid;
				$j = json_encode($p,JSON_UNESCAPED_UNICODE);
				$u['personal/'.$uuid.'/resetpw_student'] = ['module' => '個人管理/導師班學生管理-回復密碼', 'content' => $j, 'note' => '回復密碼'];
			}

			if(array_key_exists($uri, $u))
				\App\UsageLogger::add($u[$uri]['module'], array_key_exists('content',$u[$uri])?$u[$uri]['content']:$this->reqParam($request), $u[$uri]['note']);
		}
*/
        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        return route('login');
    }

	protected function reqParam($request)
	{
		$p = $request->except(['_token']);
		return json_encode($p,JSON_UNESCAPED_UNICODE);
	}
}
