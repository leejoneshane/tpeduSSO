<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="Description" content="臺北市教育人員單一身份驗證登入頁面">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/form-elements.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sweetalert2.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <script src="{{ asset('js/sweetalert2.js') }}"></script>
</head>
<body>
	<!--
	<div style="border:0px; position:fixed; _position:absolute ; right:80px; top:30px; padding:4px;">
		<img onClick='showHelp()' id=helpbutton style="padding-left:5px; border=0; cursor: pointer"  src="{{ asset('img/help.png') }}">
		<div style="height: 88px;width: 88px;display: inline-block;vertical-align: top;border: 6px solid #115b88;border-radius: 50%;background: white;margin: 6px;cursor: pointer;" title="第三方應用服務專區" onclick="window.location.href='/thirdapp'"></div>
	</div>
	-->
    <div id="top-content">
	<div class="inner-bg">
        <main class="container">
            @yield('content')
        </main>
        </div>
    </div>

    <!-- Scripts -->
    @section('script')
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-backstretch/2.0.4/jquery.backstretch.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/require.js/2.3.5/require.min.js"></script>

    <script src="{{ asset('js/scripts.js') }}"></script>
    @show
    <!--[if lt IE 10]>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-placeholder/2.3.1/jquery.placeholder.min.js"></script>
    <![endif]-->
</body>
</html>
