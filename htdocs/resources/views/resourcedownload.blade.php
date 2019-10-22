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
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
</head>
<body style="font-family: 微軟正黑體;">
	<div id="page-wrapper" style="max-width: 800px;margin: 0 auto;">
		<div class="row">
			<div class="container">
				<div class="row">
					<div class="panel panel-default" style="margin-top: 15px;">
						<div class="panel-heading">
							<h4>第三方應用服務專區</h4>
						</div>
						<div style="padding: 15px 30px;border-bottom: 2px solid #CCC;">
							<div class="row">
								<div class="col-sm-6 col-xs-12" style="float: right;">
									<button type="button" class="btn btn-success" style="float: right;margin-left: 5px;" onclick="window.location.href='/'">系統登入</button>
									<button type="button" class="btn btn-success" style="float: right;" onclick="window.location.href='/thirdapp'">第三方應用查詢</button>
								</div>
								<div class="col-sm-6 col-xs-12" style="float: right;">資源下載</div>
							</div>
						</div>
						<div class="panel-body">
							<ul style="list-style: none;">
								<li>第三方應用介接申請作業</li>
								<li>
									<ul style="list-style: initial;">
										<li><a href="{{ asset('doc/臺北市教育局單一身分驗證介接申請表.pdf') }}" target="_blank">臺北市教育局單一身分驗證介接申請表</a></li>
									</ul>
								</li>
								<li>第三方應用介接API手冊</li>
								<li>
									<ul style="list-style: initial;">
										<li><a href="{{ asset('doc/臺北市教育人員單一身分驗證資料介接手冊V2.0.docx') }}" target="_blank">臺北市教育人員單一身分驗證資料介接手冊V2.0</a></li>
										<li><a href="{{ asset('doc/臺北市教育人員單一身分驗證資料介接手冊.docx') }}" target="_blank">臺北市教育人員單一身分驗證資料介接手冊</a></li>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <!-- Scripts -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js" integrity="sha384-V77uvFToejgai7U11Acao1D9hSrQKaE84nHcMJL2NGwyejiDzHD8DJDxY5cLEANZ" crossorigin="anonymous"></script>
	<script src="//unpkg.com/axios/dist/axios.min.js"></script>
	<link href="{{ asset('assets/stylesheets/style.css') }}" rel="stylesheet">
</body>
</html>