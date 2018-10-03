<?php

namespace App\Providers;

use Log;
use Config;
use Illuminate\Support\ServiceProvider;

class SimsServiceProvider extends ServiceProvider
{
    private static $oauth_ps = null;
    private static $oauth_js = null;
    private static $seme = null;

    public function __construct()
    {
        if (is_null(self::$oauth_ps))
            self::$oauth_ps = new \GuzzleHttp\Client([
                'base_uri' => Config::get('sims.ps.base_uri'),
            ]);
        self::$seme = $this->seme();
    }

    public function ps_send($url)
    {
        //AES-128-CBC
        $p = md5(Config::get('sims.ps.oauth_secret'), true);
        $m = 'aes-128-cbc';
        $iv = md5(Config::get('sims.ps.aes_iv') . date('YmdH'), true);
        $e = base64_encode(openssl_encrypt($url, $m, $p, OPENSSL_ZERO_PADDING, $iv));

        $response = self::$oauth_ps->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Special key '.Config::get('sims.ps.oauth_id'),
                'SpecialVerify' => $e,
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);

/*        $response = self::$oauth_ps->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Special ip '.Config::get('sims.ps.oauth_id'),
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);*/
        return $response;
    }

    public function ps_call($info, array $replacement)
    {
        if (!is_array($replacement)) return;
        $search = array();
        $values = array();
        foreach ($replacement as $key => $data) {
            $search[] = "{$key}";
            $values[] = $data;
        }
        $search[] = "{seme}";
        $values[] = self::$seme;
        $url = str_replace($search, $values, Config::get("sims.ps.$info"));
        $res = $this->ps_send($url);
        $json = json_decode((string) $res->getBody());
        if ($json->status == 'ok') {
            return $json->list;
        } else {
            if (Config::get('sims.ps.debug')) Log::debug('Oauth call:'.$url.' failed! Server response:'.$json->error);
            return false;
        }
    }

    private function seme() {
        if (date('m') > 7) {
          $year = date('Y') - 1911;
          $seme = 1;
        }
        elseif (date('m') < 2) {
          $year = date('Y') - 1912;
          $seme = 1;
        }
        else {
          $year = date('Y') - 1912;
          $seme = 2;
        }
        return $year.$seme;
    }
};
