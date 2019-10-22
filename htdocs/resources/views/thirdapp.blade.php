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
	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
    <!-- Font Awesome JS -->
    <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
</head>
<body style="font-family: 微軟正黑體;">
	<div id="page-wrapper" style="max-width: 1200px;margin: 0 auto;">
		<div class="row">
			<div class="container">
				<div class="row">
					<div class="panel panel-default" style="margin-top: 15px;">
						<div class="panel-heading">
							<h4>第三方應用服務專區</h4>
						</div>
						<div style="padding: 15px;border-bottom: 2px solid #CCC;">
							<div class="row">
								<div class="col-md-4 col-sm-12 col-xs-12" style="float: right;margin-bottom: 8px;">
									<button type="button" class="btn btn-success" style="float: right;margin-left: 5px;" onclick="window.location.href='/'">系統登入</button>
									<button type="button" class="btn btn-success" style="float: right;" onclick="window.location.href='/resourcedownload'">資源下載</button>
								</div>
								<div class="col-md-8 col-sm-12 col-xs-12" style="float: right;margin-bottom: 8px;">
									<form action="thirdapp">
										@csrf
										<input type="text" name="entry" value="{{ $entry }}" maxlength="20" style="margin: 0 8px;" placeholder="關鍵字查詢"/>
										<button type="submit" class="btn btn-success">查詢</button>
									</form>
								</div>
							</div>
						</div>
						<div class="panel-body">
							<table class="table table-hover" style="margin: 0;">
								<thead>
									<tr>
										<th>應用平臺名稱</th>
										<th>應用平臺網址</th>
										<th>平臺說明</th>
										<th>申請單位</th>
										<th>業務聯絡窗口</th>
									</tr>
								</thead>
								<tbody>
								@if (!empty($apps))
								@foreach ($apps as $app)
									<tr>
										<td style="vertical-align: inherit;">
											<label>{{ $app['entry'] }}</label>
										</td>
										<td style="vertical-align: inherit;">
											<label><a href="{{ $app['url'] }}" target="_blank">{{ $app['url'] }}</a></label>
										</td>
										<td style="vertical-align: inherit;">
											<label>{{ $app['background'] }}</label>
										</td>
										<td style="vertical-align: inherit;">
											<label>{{ $app['unit'] }}</label>
										</td>
										<td style="vertical-align: inherit;">
											<label>{{ $app['contel'] }}<br/>{{ $app['conman'] }}</label>
										</td>
									</tr>
								@endforeach
								@endif
								</tbody>
							</table>
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
