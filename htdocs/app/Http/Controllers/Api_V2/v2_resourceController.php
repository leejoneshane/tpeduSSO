<?php

namespace App\Http\Controllers\Api_V2;

use Cookie;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Token;
use Laravel\Passport\Client;
use App\User;

class v2_resourceController extends Controller
{
	public function valid_token(Request $request, $token)
    {
		$psr = (new \Lcobucci\JWT\Parser())->parse($token);
		$token_id = $psr->getClaim('jti');
		$token = Token::where('id', $token_id)->get();
		$user = $token->user;

		$validate = array();
		if (isset($user->uuid)) $validate['user'] = $user->uuid;
		$validate['personal'] = false;
		if (!empty($token->name)) $validate['personal'] = true;
		$validate['client_id'] = $token->client_id;
		$validate['scopes'] = $token->scopes;

		return response()->json($validate);
	}
}
