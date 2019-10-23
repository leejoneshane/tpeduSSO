@extends('layouts.superboard')

@section('page_heading')
<h1 class="page-header">系統作業日誌查詢</h1>
@endsection

@section('section')
<style>
#urtbody .hold {
	max-width: 222px;
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;
}
@media (max-width: 1200px) {
	#urtbody .hold {
		max-width: 140px;
	}
}
@media (max-width: 768px) {
	#urtbody .hold {
		max-width: 90px;
	}
}
</style>
<div class="container">
	<form id="query" action="{{ route('bureau.usagerecord') }}" method="POST">
	<div class="row">
		<div class="panel panel-default">	  
			<div class="panel-heading" style="overflow: hidden;">
				<div style="float: right;margin-top: 3px;">
					<button type="button" class="btn btn-success" style="margin-left: 4px;" onclick="$('#query').submit();">查詢</button>
				</div>
				<div style="float: left;">
					作業日誌期間：
					<input type="text" name="dt1" value="{{ old('dt1') }}" maxlength="10" style="width: 100px;" placeholder="yyyymmdd"/>
					<span class="span-calendar add-on"><i class="glyphicon glyphicon-calendar"></i></span>
					～
					<input type="text" name="dt2" value="{{ old('dt2') }}" maxlength="10" style="width: 100px;" placeholder="yyyymmdd"/>
					<span class="span-calendar add-on"><i class="glyphicon glyphicon-calendar"></i></span>
				</div>
			</div>
			<div class="panel-body">
				<table class="table table-hover" style="margin: 0;">
					<thead>
						<tr>
							<th>登入人員</th>
							<th>登入IP</th>
							<th>作業時間</th>
							<th>作業模組</th>
							<th>作業內容</th>
							<th>備註</th>
						</tr>
						<tr style="background-color: rgba(40, 180, 240, 0.2);">
							<th><input type="text" name="user" style="width: 100%" value="{{ old('user') }}" /></th>
							<th><input type="text" name="ip" style="width: 100%" value="{{ old('ip') }}" /></th>
							<th>期間設定如上</th>
							<th><input type="text" name="module" style="width: 100%" value="{{ old('module') }}" /></th>
							<th><input type="text" name="content" style="width: 100%" value="{{ old('content') }}" /></th>
							<th><input type="text" name="note" style="width: 100%" value="{{ old('note') }}" /></th>
						</tr>
					</thead>
					<tbody id="urtbody">
					@if (!empty($data))
					@foreach ($data as $d)
						<tr>
							<td style="vertical-align: inherit;">
								<label>{{ $d['username'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['ipaddress'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ date('Y-m-d', strtotime($d['created_at'])) }}<br/>{{ date('H:i:s', strtotime($d['created_at'])) }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['module'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<a href="#showcontent" data-toggle="modal" onclick="$('#showcontent').find('.panel-body').text($(this).find('label').text());"><label class="hold">{{ $d['content'] }}</label></a>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['note'] }}</label>
							</td>
						</tr>
					@endforeach
					@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
	@csrf
	</form>

	<div id="showcontent" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<div class="panel-heading">作業內容</div>
					<div class="panel-body" style="word-break: break-all;"></div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection