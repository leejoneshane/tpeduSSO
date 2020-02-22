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
	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
	<link href="{{ asset('assets/stylesheets/styles.css') }}" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
</head>
<body>
    <ul id="app">
        <nav class="navbar navbar-default navbar-static-top" style="margin-bottom: 0">
			<div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
			</div>
            <ul class="nav navbar-top-links navbar-right">
                @guest
                    <li><a href="{{ route('login') }}">登入</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user fa-fw"></i>{{ Auth::user()->name }} 
                        </a>
                        <ul  style="min-width:50px; left: -40px; top:120%" class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ url('/') }}"><i class="fa fa-home fa-fw"></i>回首頁</a></li>
                            @if (Auth::user()->is_admin || Auth::user()->id == 1)
                                <li><a class="dropdown-item" href="{{ route('sync') }}"><i class="fa fa-gears fa-fw"></i>資料維護</a></li>
                        	    <li><a class="dropdown-item" href="{{ route('bureau') }}"><i class="fa fa-eye fa-fw"></i>局端管理</a></li>
                    	    @endif
                            @if (isset(Auth::user()->ldap['adminSchools']))
                                @foreach (Auth::user()->ldap['adminSchools'] as $o)
                                    <li><a class="dropdown-item" href="{{ route('school', [ 'dc' => $o ]) }}"><i class="fa fa-university fa-fw"></i>學校管理：{{ $o }}</a></li>
                                @endforeach
                            @endif
                            @if (isset(Auth::user()->ldap['tpTutorClass']))
                                <?php $tutor = Auth::user()->ldap['tpTutorClass']; ?>
                                <li><a class="dropdown-item" href="{{ route('tutor', [ 'dc' => $o, 'class' => $tutor ]) }}"><i class="fa fa-library fa-fw"></i>班級管理：{{ $tutor }}</a></li>
                            @endif
                            @if (!(Auth::user()->is_parent))
                                <li><a class="dropdown-item" href="{{ route('oauth') }}"><i class="fa fa-key fa-fw"></i>金鑰管理</a></li>
                            @endif
                            @if (Auth::user()->ldap['employeeType'] != '學生')
                                <li><a class="dropdown-item" href="{{ route('parent.showAuthProxyForm') }}"><i class="fa fa-unlock fa-fw"></i>代理授權</a></li>
                                <li><a class="dropdown-item" href="{{ route('parent.listLink') }}"><i class="fa fa-child fa-fw"></i>親子連結</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fa fa-edit fa-fw"></i>修改個資</a></li>
                            @if (!(Auth::user()->is_parent))
                                <li><a class="dropdown-item" href="{{ route('changeAccount') }}"><i class="fa fa-tag fa-fw"></i>變更帳號</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('changePassword') }}"><i class="fa fa-lock fa-fw"></i>變更密碼</a></li>
                            <li><a class="dropdown-item" href="{{ route('socialite') }}"><i class="fa fa-at fa-fw"></i>社群帳號綁定</a></li>
                            <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="fa fa-sign-out fa-fw"></i>登出</a></li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </ul>
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
