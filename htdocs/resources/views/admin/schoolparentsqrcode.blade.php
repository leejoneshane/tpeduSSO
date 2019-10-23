<html>
<head>
</head>
<body>
@if ($error)
	<div class="alert alert-danger">{{ $error }}</div>
@endif
	<div style="width: 649px;">
		@if (!empty($data))
		@foreach ($data as $d)
			<table style="width: 100%;height: 140px;margin-bottom: 10px;font-family: 微軟正黑體;font-size: 27px;border: 1px solid #CCC;">
				<tr>
					<td>
						<div style="float: left;width: 25%;">{{ $d->cls }}班<br/>{{ $d->seat }}號</div>
						<div style="float: left;width: 25%;">{{ $d->sname }}</div>
						<div style="float: left;width: 25%;">{{ $d->name }}</div>
						<div style="float: left;width: 25%;">{{ $d->rel }}</div>
					</td>
					<td rowspan="2" width="1">
						<img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(250)->margin(0)->encoding('UTF-8')->generate($d->guid)) !!} " style="height: 140px;">
					</td>
				</tr>
				<tr>
					<td>
						<div style="text-align: right;">{{ $dt }}</div>
					</td>
				</tr>
			</table>
			@if ($loop->index % 6 == 5)
				<!--<p style="page-break-before: always;"></p>-->
			@endif
		@endforeach
		@endif
	</div>
<script type="text/javascript">
	@if (!$error)
		document.addEventListener("DOMContentLoaded", () => {
			window.print();
		});
	@endif
</script>
</body>
</html>