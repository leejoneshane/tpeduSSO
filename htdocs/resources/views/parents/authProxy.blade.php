@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
	<div class="col-md-8 col-md-offset-2">
	    <div class="card card-default" style="margin-top: 20px">
		<div class="card-header">12歲以下學童個資授權家長同意功能</div>
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
		<form id="query" action="{{ route('parents.authConnectChild') }}" method="POST">
		
			<div class="form-group">
			 <label for="displayName" class="col-md-4 text-md-right control-label">選擇你12歲以下的小孩</label>
				<div class="row">
					<div class="col-md-6 text-md-left">
					<select name="student" class="form-control" style="width: auto"  onchange="location='{{ url()->current() }}?student=' + $(this).val();">
						@if (!empty($dataList))
						@foreach ($dataList as $dd)
							<option value="{{ $dd['wantAgree'] ? $dd['id']:'' }}" {{ $dd['isChecked'] }}>{{ $dd['student_name'] }}</option>
						@endforeach
						@endif	
					</select>
					</div>
				</div>
		    </div>

			<div class="row">
					<div class="col-md-10 text-md-left control-label">
					<label><input id="agreeAll" name="agreeAll" type="checkbox" value="1" {{ $agreeAll ? 'checked':'' }} > 
					概括同意我的小孩子得授權給任何第三方應用(含日後新增)存取其個資內容以下第三方應用。個別同意你的小孩得授權限給第三方應用存取其個資內容
					</label>
					</div>
			</div>					

		<div class="col-md-10 col-md-offset-5">
			<button type="submit" class="btn btn-primary" id='buttonSubmit' name='buttonSubmit' value="send">確定</button>
		</div>		

		<div class="row">
			<div class="panel-body">
				<table class="table table-hover" style="margin: 0;">
					<thead>
						<tr>
                            <th>同意</th>
                            <th>第三方應用系統</th>
							<th>應用系統說明</th>
							<th>存取個資</th>
						</tr>
					</thead>
					<tbody id="urtbody">
					@if (!empty($apps))
						@foreach ($apps as $app)
						<tr>
							<td style="vertical-align: inherit;">
							<input id="agree" name="agree[]" type="checkbox" value="{{ $app['id'] }}" {{ ($app['agree'] or $agreeAll) ? 'checked':'' }} {{ $agreeAll ? 'disabled':'' }} >
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $app['entry'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $app['background'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $app['scope_list'] }}</label>
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
	    </div>
	</div>
    </div>

</div>
@endsection
@section('script')
@parent
<script type="text/javascript">
$(document).ready(function(){
	$('#agreeAll').click(function(){
		if ($("#agreeAll").prop("checked")){
			$("input[name='agree[]']").each(function() {
				$(this).prop("checked", true);
				$(this).prop("disabled", true);
			});
		}else{
			$("input[name='agree[]']").each(function() {
				$(this).prop("checked", false);
				$(this).prop("disabled", false);
			});		}
	});
});
</script>
@endsection

