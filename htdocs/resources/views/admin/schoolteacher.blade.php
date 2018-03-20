@extends('layouts.dashboard')

@section('page_heading')
教師管理
@endsection

@section('section')
<div class="container">
	<div class="row">
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
			<select id="field" name="field" class="form-control" style="width: auto" onchange="if ($(this).val().substr(0,3) == 'ou=') location='{{ url()->current() }}?field=' + $(this).val();">
			    <option value="uuid" {{ $my_field == 'uuid' ? 'selected' : '' }}>使用者唯一編號</option>
			    <option value="idno" {{ $my_field == 'idno' ? 'selected' : '' }}>身分證字號</option>
			    <option value="name" {{ $my_field == 'name' ? 'selected' : '' }}>姓名</option>
			    <option value="mail" {{ $my_field == 'mail' ? 'selected' : '' }}>電子郵件</option>
			    <option value="mobile" {{ $my_field == 'mobile' ? 'selected' : '' }}>手機號碼</option>
				@foreach ($ous as $ou => $desc)
			    	<option value="ou={{ $ou }}" {{ $my_field == 'ou='.$ou ? 'selected' : '' }}>{{ $desc }}</option>
			    @endforeach
			    <option value="ou=deleted" {{ $my_field == 'ou=deleted' ? 'selected' : '' }}>已刪除</option>
			</select>
        	<input type="text" class="form-control" style="width:auto" id="keywords" name="keywords" placeholder="搜尋..." value="{{ old('keywords') }}">
            <span class="input-group-btn" style="width: auto">
            	<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?field=' + $('#field').val() + '&keywords=' + $('#keywords').val();">
            		<i class="fa fa-search"></i>
            	</button>
        	</span>
    	</div>
	</div>
	<div class="col-sm-12">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>
				教師一覽表
			</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>狀態</th>
						<th>UUID</th>
						<th>身分證字號</th>
						<th>姓名</th>
						<th>單位</th>
						<th>職稱</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@for ($i=0;$i<$teachers['count'];$i++)
					<tr>
						<td style="vertical-align: inherit;">
							<span>{{ $teachers[$i]['inetuserstatus'][0] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $teachers[$i]['entryuuid'][0] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $teachers[$i]['cn'][0] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>
							<span>{{ $teachers[$i]['displayname'][0] }}</span>
							</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $teachers[$i]['department'][0] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $teachers[$i]['titlename'][0] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<button type="button" class="btn btn-primary"
							 	onclick="$('#form').attr('action','{{ route('school.updateTeacher', [ 'uuid' => $teachers[$i]['entryuuid'][0] ]) }}');
										 $('#form').attr('method', 'GET');
										 $('#form').submit();">編輯</button>
							@if ($teachers[$i]['inetuserstatus'][0] != '已刪除')
							<button type="button" class="btn btn-warning"
							 	onclick="$('#form').attr('action','{{ route('school.toggleTeacher', [ 'uuid' => $teachers[$i]['entryuuid'][0] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">{{ $teachers[$i]['inetuserstatus'][0] == '啟用' ? '停用' : '啟用' }}</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('school.removeTeacher', [ 'uuid' => $teachers[$i]['entryuuid'][0] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">刪除</button>
							@else
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('school.undoTeacher', [ 'uuid' => $teachers[$i]['entryuuid'][0] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">救回</button>
							@endif
						</td>
					</tr>
					@endfor
					<form id="form" action="" method="" style="display: none;">
					@csrf
    				</form>
				</tbody>
			</table>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection