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
				<div class="col-md-16">
					@if (empty($myidno))
					<p>請先進行親子連結後再進行代理授權設定，謝謝！</p>
					@else
					<form id="form" action="{{ route('parent.applyAuthProxy') }}" method="POST">
					@csrf
					<div class="input-group custom-search-form">
						<label for="student" class="control-label">請選擇您13歲以下的小孩：</label>
						<select name="student" class="form-control pull-right" style="width: auto"  onchange="location='{{ url()->current() }}?myidno=' + $(this).val();">
						   @if ($kids)
							   @foreach ($kids as $idno => $name)
								   <option value="{{ $idno }}"{{ ($myidno == $idno) ? ' selected' : '' }}>{{ $name }}</option>
							   @endforeach
						   @endif	
				   		</select>
					</div>
					<input type="hidden" name="student" value="{{ $myidno }}">
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
