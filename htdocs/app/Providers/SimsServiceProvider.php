<?php

namespace App\Providers;

use Log;
use Config;
use Illuminate\Support\ServiceProvider;

class SimsServiceProvider extends ServiceProvider
{
    private static $oauth_ps = null;
    private static $oauth_js = null;

    public function __construct()
    {
        if (is_null(self::$oauth_ps))
            $this->connect();
    }

    public function error()
    {
        if (is_null(self::$oauth_ps)) return;
        return self::$oauth_ps['response'];
    }

    public function connect()
    {
    }
};
