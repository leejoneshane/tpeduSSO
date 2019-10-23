@extends('layouts.superboard')

@section('page_heading')
<h1 class="page-header">使用者授權同意日誌查詢</h1>
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
	<form id="query" action="{{ route('bureau.OauthScopeAccessLog') }}" method="POST">
	<div class="row">
		<div class="panel panel-default">	  
			<div class="panel-heading" style="overflow: hidden;">
				<div style="float: right;margin-top: 3px;">
					<button type="button" class="btn btn-success" style="margin-left: 4px;" onclick="$('#query').submit();">查詢</button>
				</div>
				<div style="float: left;">
					授權期間：
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
							<th>授權人</th>
							<th>同意人</th>
							<th>第三方應用</th>
							<th>授權時間</th>
							<th>授權範圍</th>
							<th>資料內容</th>
						</tr>
						<tr style="background-color: rgba(40, 180, 240, 0.2);">
							<th><input type="text" name="authorizer" style="width: 100%" value="{{ old('authorizer') }}" /></th>
							<th><input type="text" name="approve" style="width: 100%" value="{{ old('approve') }}" /></th>
							<th><input type="text" name="entry" style="width: 100%" value="{{ old('entry') }}" /></th>
							<th>期間設定如上</th>
							<th><input type="text" name="scope" style="width: 100%" value="{{ old('scope') }}" /></th>
							<th><input type="text" name="scope_range" style="width: 100%" value="{{ old('scope_range') }}" /></th>
						</tr>
					</thead>
					<tbody id="urtbody">
					@if (!empty($data))
					@foreach ($data as $d)
						<tr>
							<td style="vertical-align: inherit;">
								<label>{{ $d['authorizer'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['approve'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['entry'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ date('Y-m-d', strtotime($d['created_at'])) }}<br/>{{ date('H:i:s', strtotime($d['created_at'])) }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['scope'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['scope_range'] }}</label>
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
</div>
@endsection