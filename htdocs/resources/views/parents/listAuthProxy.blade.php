@extends('layouts.app')

@section('content')
<script src="https://cdnjs.com/libraries/bootstrap-slider"></script>
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

				<div class="col-md-10 col-md-offset-9">
					<a class="btn btn-primary" id='buttonAdd' name='buttonAdd' href='{{ route('parent.showAuthForm') }}'>新增個資授權</a>
				</div>		
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
				<table class="table table-hover">
					<thead>
						<tr>
							<th>授權對象</th>
							<th>信任等級</th>
							<th>授權範圍</th>
							<th>管理</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($authorizes as $a)
						<tr>
							<form id="form" action="" method="POST">
							@csrf
							<td style="vertical-align: inherit;">
								<span>{{ $a->client_id == '*' ? '概括授權' : $a->client()->name }}</span>
							</td>
							<td style="vertical-align: inherit;">
								@if ($a->client_id != '*')
								<input id="level" type="text" value="{{ $a->trust_level }}"
									data-provide="slider"
									data-slider-ticks="[0, 1, 2, 3]"
									data-slider-ticks-labels='["僅公開資訊", "一般資訊", "敏感資訊", "完全信任"]'
									data-slider-min="0"
									data-slider-max="3"
									data-slider-step="1"
									data-slider-value="{{ $a->trust_level }}"
									data-slider-tooltip="hide"
									onchange="refresh_help()"/>
								<button type="button" class="btn btn-primary"
									onchange="$('#form').attr('action','{{ route('parent.updateAuthProxy', [ 'id' => $a->id ]) }}'); $('#form').submit();">
									修改
								</button>

								@endif
							</td>
							<td style="vertical-align: inherit;">
								<span id="help"></span>
							</td>
							<td style="vertical-align: inherit;">
								<button type="button" class="btn btn-danger"
									onchange="$('#form').attr('action','{{ route('parent.removeAuthProxy', [ 'id' => $a->id ]) }}'); $('#form').submit();">
									刪除
								</button>
							</td>
							</form>
						</tr>
						@endforeach
					</tbody>
				</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	function refresh_help() {
		$a = $('#level').val();
		if ($a == 0) $('#help').text('{{ $trust_level[0] }}');
		if ($a == 1) $('#help').text('{{ $trust_level[1] }}');
		if ($a == 2) $('#help').text('{{ $trust_level[2] }}');
		if ($a == 3) $('#help').text('{{ $trust_level[3] }}');
	}
</script>
@endsection
