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
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" integrity="sha384-604wwakM23pEysLJAhja8Lm42IIwYrJ0dEAqzFsj9pJ/P5buiujjywArgPCi8eoz" crossorigin="anonymous">
	<!--<link href="{{ asset('css/app.css') }}" rel="stylesheet">-->
	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
	<!--<link href="{{ asset('assets/stylesheets/styles.css') }}" rel="stylesheet">-->
	<link href="{{ asset('css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">

	<style>
		span.add-on {
			display: inline-block;
			padding: 4px 5px;
			line-height: 20px;
			background-color: #eee;
			border: 1px solid #ccc;
			margin-left: -4px;
			border-bottom-right-radius: 4px;
			border-top-right-radius: 4px;
			cursor: pointer;
		}
	</style>

    <!-- Font Awesome JS -->
    <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
</head>
<body style="font-family: 微軟正黑體;">
    <div id="app" class="wrapper">
        @yield('content')

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light" style="background-color: white;padding: 0;margin: 0;">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info" style="margin: 15px 10px;">
						<i class="glyphicon glyphicon-menu-hamburger"></i>
                        <span></span>
                    </button>
                    <div style="float: right;">
                        <ul class="nav navbar-top-links navbar-right">
                        @guest
                            <li><a href="{{ route('login') }}">登入</a></li>
                        @else
							@if (Auth::user()->is_admin || Auth::user()->id == 1 || Auth::user()->ldap['adminSchools'])
								<li class="dropdown">
									<a id="userMenu" class="dropdown-toggle" href="#" data-toggle="dropdown">
										<i class="fa fa-user fa-fw"></i>{{ Auth::user()->name }} <span class="caret"></span>
									</a>
									<ul class="dropdown-menu" style="min-width:50px;right: 0;left: auto;top: 65px;">
										<!--<li><a class="dropdown-item" href="{{ url('/') }}"><i class="fa fa-home fa-fw"></i>回首頁</a></li>-->
										<li><a class="dropdown-item" href="{{ url('/') }}"><i class="glyphicon glyphicon-user" style="margin: 0 1px 0 2px;"></i>個人管理</a></li>
										@if (Auth::user()->is_admin || Auth::user()->id == 1)
										<li><a class="dropdown-item" href="{{ route('sync') }}"><i class="fas fa-cogs fa-fw"></i>資料維護</a></li>
										<li><a class="dropdown-item" href="{{ route('bureau') }}"><i class="fa fa-eye fa-fw"></i>局端管理</a></li>
										@endif
										@if (Auth::user()->ldap['adminSchools'])
										@foreach (Auth::user()->ldap['adminSchools'] as $o)
										<li><a class="dropdown-item" href="{{ route('school', [ 'dc' => $o ]) }}"><i class="fa fa-university fa-fw"></i>學校管理：{{ $o }}</a></li>
										@endforeach
										@endif
										<li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
												document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt fa-fw"></i>登出</a></li>
										<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
											@csrf
										</form>
									</ul>
								</li>
							@else
								<li>
									<a id="userMenu" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
										<i class="fa fa-user fa-fw"></i>{{ Auth::user()->name }}&nbsp;&nbsp;登出
									</a>
								</li>
								<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
									@csrf
								</form>
							@endif
                        @endguest
                        </ul>
                    </div>
                </div>
            </nav>

        	<div id="page-wrapper">
				<div class="row">
                	<div class="col-lg-12">
                    	@yield('page_heading')
                	</div>
           		</div>
				<div class="row">  
					@yield('section')
            	</div>
        	</div>
        </div>
    </div>

    <!-- Scripts -->
	@if (Request::is('oauth'))
	<style>div .modal-body { overflow: hidden; }</style>
	<script src="{{ asset('js/app.js') }}"></script>
	@endif
	@section('script')
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js" integrity="sha384-V77uvFToejgai7U11Acao1D9hSrQKaE84nHcMJL2NGwyejiDzHD8DJDxY5cLEANZ" crossorigin="anonymous"></script>
	<!--<script src="{{ asset('js/bootstrap.min.js') }}"></script>-->
	<script src="//unpkg.com/axios/dist/axios.min.js"></script>
	<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
	<script src="{{ asset('js/bootstrap-datepicker.zh-TW.min.js') }}"></script>
	@show
    <script type="text/javascript">
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });

			$("#sidebar").find("a.func-item").click(function(){
				$("#sidebar").find("li.active").removeClass('active');
				$(this).parent().addClass('active');
			})

			$('.calendar').datepicker({format: "yyyymmdd",language: 'zh-TW'});
			$('.span-calendar').each(function() {
				var $picker = $(this);
				$picker.datepicker({format: "yyyymmdd",language: 'zh-TW'}).datepicker('setDate',new Date());
				var pickerObject = $picker.data('datepicker');
				$picker.on('changeDate', function(ev){
					var d = ev.date;
					$picker.prev().val(ev.date.getFullYear()*10000+(ev.date.getMonth()+1)*100+ev.date.getDate());
					$picker.datepicker('hide');
				});
			});
		});
    </script>
	<link href="{{ asset('assets/stylesheets/style.css') }}" rel="stylesheet">
</body>
</html>