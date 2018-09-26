<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class oauthController extends Controller
{
    public function __construct()
    {
        //
    }

    public function index()
    {
        return view('oauthManager');
    }
}
