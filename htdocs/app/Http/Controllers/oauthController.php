<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class oauthController extends Controller
{
	protected $server;
	protected $tokens;

	public function __construct(ResourceServer $server, TokenRepository $tokens)
    {
		$this->server = $server;
		$this->tokens = $tokens;
    }

    public function index()
    {
        return view('oauthManager');
    }

	public function oauthResource(Request $request)
	{
		$json = array();
		$token = $request->bearerToken();

		if(!empty($token)){
			try{
				$psr = (new DiactorosFactory)->createRequest($request);
				$psr = $this->server->validateAuthenticatedRequest($psr);
				$token = $this->tokens->find($psr->getAttribute('oauth_access_token_id'));

				$json['active'] = true;
				$json['client_id'] = $token['client_id'];

				if(is_int($token['client_id']) || ctype_digit($token['client_id'])){
					$names = \DB::select('select name from oauth_clients where id = ?', array($token['client_id']));
					if(count($names) > 0)
						$json['username'] = $names[0]->name;
				}

				if(is_array($token['scopes']))
					$json['scope'] = implode(" ",$token['scopes']);
			} catch (OAuthServerException $e) {
			}
		}else{
			return redirect('/');
		}

		if(!array_key_exists('active',$json))
			$json['active'] = false;

		return json_encode($json, JSON_UNESCAPED_UNICODE);
	}
}