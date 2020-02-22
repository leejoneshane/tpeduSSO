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
				<div class="col-sm-12">
					<div class="input-group custom-search-form">
						<div class="form-group">
							<label for="student" class="col-md-4 text-md-right control-label">請選擇您13歲以下的小孩：</label>
							<div class="row">
								<div class="col-md-6 text-md-left">
									<select name="student" class="form-control" style="width: auto"  onchange="location='{{ url()->current() }}?myidno=' + $(this).val();">
									   @if ($kids)
										   @foreach ($kids as $idno => $name)
											   <option value="{{ $idno }}"{{ ($myidno == $idno) ? ' selected' : '' }}>{{ $name }}</option>
										   @endforeach
									   @endif	
							   		</select>
							   </div>
							</div>
						</div>
					</div>
					<form id="form" action="{{ route(parent.applyAuthProxy) }}" method="POST">
					@csrf
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
								<input name="agree[]" type="checkbox" value="{{ $app->client_id }}">
							</td>
							<td style="vertical-align: inherit;">
								<input id="{{ $app->client_id }}level" type="text" value="2"
									data-provide="slider"
									data-slider-ticks="[0, 1, 2, 3]"
									data-slider-ticks-labels='["僅公開資訊", "一般資訊", "敏感資訊", "完全信任"]'
									data-slider-min="0"
									data-slider-max="3"
									data-slider-step="1"
									data-slider-value="2"
									data-slider-tooltip="hide"
									onchange="refresh_help()"/>
							</td>
							<td style="vertical-align: inherit;">
								<span id="help"></span>
							</td>
						</tr>
						@endforeach
						<td style="vertical-align: inherit;">
							<button type="submit" class="btn btn-danger">確定</button>
						</td>
					</tbody>
					</table>
					</form>
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

	function refresh_help() {
		$a = $('#level').val();
		if ($a == 0) $('#help').text('{{ $trust_level[0] }}');
		if ($a == 1) $('#help').text('{{ $trust_level[1] }}');
		if ($a == 2) $('#help').text('{{ $trust_level[2] }}');
		if ($a == 3) $('#help').text('{{ $trust_level[3] }}');
	}
</script>
@endsection
