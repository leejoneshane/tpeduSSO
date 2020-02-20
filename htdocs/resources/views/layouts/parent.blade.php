<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel" style="margin-bottom: 0">
            <div class="container-fluid">
			<div class="navbar-header">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
			</div>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav navbar-left">
				</ul>

                <!-- Right Side Of Navbar -->
				<ul class="navbar-nav navbar-right ml-auto">
                        @guest
                            <li><a class="nav-link" href="{{ route('login') }}">登入</a></li>
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-user fa-fw"></i>{{ Auth::user()->name }} 
                                </a>
                                <ul  style="min-width:50px; left: -40px; top:120%" class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="{{ route('parent') }}"><i class="fa fa-home fa-fw"></i>回首頁</a></li>
                                    <li><a class="dropdown-item" href="{{ route('parent.listLink') }}"><i class="fa fa-child fa-fw"></i>親子連結</a></li>
                                    <li><a class="dropdown-item" href="{{ route('parent.showAuthProxyForm') }}"><i class="fa fa-key fa-fw"></i>代理授權</a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fa fa-edit fa-fw"></i>修改個資</a></li>
                                    <li><a class="dropdown-item" href="{{ route('changePassword') }}"><i class="fa fa-lock fa-fw"></i>變更密碼</a></li>
                                    <li><a class="dropdown-item" href="{{ route('socialite') }}"><i class="fa fa-at fa-fw"></i>社群帳號綁定</a></li>
                                    <li><a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                        	document.getElementById('logout-form').submit();"><i class="fa fa-sign-out fa-fw"></i>登出</a></li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
				</ul>
			</div>
        </nav>
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
