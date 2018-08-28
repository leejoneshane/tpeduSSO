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
                            <li><a class="nav-link" href="{{ route('register') }}">註冊（暫不開放）</a></li>
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-user fa-fw"></i>{{ Auth::user()->name }} 
                                </a>
                                <ul  style="min-width:50px; left: -40px; top:120%" class="dropdown-menu" aria-labelledby="navbarDropdown">
                            	    @if (Auth::user()->is_admin || Auth::user()->id == 1)
                            	    <li><a class="dropdown-item" href="{{ route('bureau') }}"><i class="fa fa-eye fa-fw"></i>局端管理</a></li>
                            	    @endif
                                    @if (Auth::user()->ldap['adminSchools'])
                                    @foreach (Auth::user()->ldap['adminSchools'] as $o)
                                    <li><a class="dropdown-item" href="{{ route('school', [ 'dc' => $o ]) }}"><i class="fa fa-university fa-fw"></i>學校管理：{{ $o }}</a></li>
                                    @endforeach
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('oauth') }}"><i class="fa fa-key fa-fw"></i>金鑰管理</a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fa fa-edit fa-fw"></i>修改個資</a></li>
                                    <li><a class="dropdown-item" href="{{ route('changeAccount') }}"><i class="fa fa-tag fa-fw"></i>變更帳號</a></li>
                                    <li><a class="dropdown-item" href="{{ route('changePassword') }}"><i class="fa fa-lock fa-fw"></i>變更密碼</a></li>
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
