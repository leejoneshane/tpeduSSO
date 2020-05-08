@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">代理授權一覽表</div>
                <div class="card-body">
				@if (session('error'))
		    		<div class="alert alert-danger">
					{{ session('error') }}
		    		</div>
				@endif
				@if (session('success'))
		    		<div class="alert alert-success">
					{{ session('success') }}
		    		</div>
				@endif
				<h4 class="justify-content-center">臺北市教育人員單一身分驗證個人資料政策</h4>
				<p>本服務係由臺北市教育局所建置，請你務必詳讀以下條款，謹依個人資料保護法第 8 條規定告知：</p>
				<ul>
					<li>本服務所有資料係依照『臺北市國民中小學學生學籍管理辦法』、『高級中等學校學生學籍管理辦法』取得，搜集目的為讓各級教育機構於提供各項教育行政應用服務時，透過本系統進行單一身分驗證。</li>
					<li>本服務所有資料之蒐集方式、詳細內容、利用期間、地區、對象及方式，已經由學生就讀學校製作資料字典並公開刊載於各校官方網站。如還有疑慮請逕向教育局資教科反應。</li>
					<li>服務對象中未滿 13 歲之兒童依法須由監護人行使個資同意權。您將以監護人身分代替貴子弟行使個人資料保護法明訂之權益，包含：查詢或請求閱覽、請求補充或更正、請求停止提供資料給第三方應用...等。</li>
				</ul>
				</p>
				<hr>
				<div class="col-md-16">
					@if (empty($student))
					<p>請先進行親子連結後再進行代理授權設定，謝謝！</p>
					@else
					<div class="input-group custom-search-form">
						<label for="PSlink" class="control-label">請選擇您13歲以下的小孩：</label>
						<select name="PSlink" class="form-control pull-right" style="width: auto"  onchange="location='{{ url()->current() }}?id=' + $(this).val();">
						   @if ($kids)
							   @foreach ($kids as $id => $kid)
								   <option value="{{ $id }}"{{ ($student == $kid['idno']) ? ' selected' : '' }}>{{ $kid['name'] }}</option>
							   @endforeach
						   @endif	
				   		</select>
					</div>
					<form id="form" action="{{ route('parent.guardianAuth') }}" method="POST">
					@csrf
					<input type="hidden" name="student" value="{{ $student }}">
					<div class="row">
						<div class="col-md-10 text-md-left control-label">
							<label><input id="agreeAll" name="agreeAll" type="checkbox" value="{{ $agreeAll ? $agreeAll->id : 'new' }}"{{ $agreeAll ? ' checked' : '' }} onclick="swap()">
								概括同意我的小孩得授權給任何第三方應用（含日後新增）。
							</label>
						</div>
					</div>
					<table id="listall" class="table table-hover">
					<thead>
						<tr>
                            <th>同意</th>
                            <th>應用服務名稱</th>
							<th>信任等級</th>
							<th>授權範圍說明</th>
						</tr>
					</thead>
					<tbody>
					@if ($apps)
						@foreach ($apps as $app)
						<tr>
							<td style="vertical-align: inherit;">
								<input name="agree[]" type="checkbox" value="{{ $app->id }}">
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $app->name }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<select name="{{ $app->id }}level" class="form-control" style="width: auto" onchange="refresh_help('{{ $app->id }}');">
									<option value="0"{{ isset($authorizes[$app->id]) && $authorizes[$app->id] == 0 ? ' selected' : '' }}>公開資訊</option>
									<option value="1"{{ isset($authorizes[$app->id]) && $authorizes[$app->id] == 1 ? ' selected' : '' }}>一般資訊</option>
									<option value="2"{{ isset($authorizes[$app->id]) && $authorizes[$app->id] == 2 ? ' selected' : '' }}>敏感資訊</option>
									<option value="3"{{ isset($authorizes[$app->id]) && $authorizes[$app->id] == 3 ? ' selected' : '' }}>完全信任</option>
								</select>
							</td>
							<td style="vertical-align: inherit;">
								<span id="{{ $app->id }}help">{{ isset($authorizes[$app->id]) ? $trust_level[$authorizes[$app->id]] : '' }}</span>
							</td>
						</tr>
						@endforeach
					@endif
					</tbody>
					</table>
					<button type="submit" class="btn btn-danger">確定</button>
					</form>
					@endif
				</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	function swap() {
		if ($('#agreeAll').prop("checked")) {
			$('#listall').hide();
		} else {
			$('#listall').show();
		}
	}

	function refresh_help(target) {
		$a = $('#' + target + 'level').val();
		if ($a == 0) $('#' + target + 'help').text('{{ $trust_level[0] }}');
		if ($a == 1) $('#' + target + 'help').text('{{ $trust_level[1] }}');
		if ($a == 2) $('#' + target + 'help').text('{{ $trust_level[2] }}');
		if ($a == 3) $('#' + target + 'help').text('{{ $trust_level[3] }}');
	}
</script>
@endsection
