@extends('layouts.superboard')

@section('page_heading')
人員管理
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
			<select id="area" name="area" class="form-control" style="width: auto" onchange="location='{{ url()->current() }}?area=' + $(this).val();">
				@foreach ($areas as $st)
			    	<option value="{{ $st }}"{{ $area == $st ? ' selected' : '' }}>{{ $st }}</option>
			    @endforeach
			</select>
			<select id="dc" name="dc" class="form-control" style="width: auto" onchange="location='{{ url()->current() }}?area=' + $('#area').val() + '&dc=' + $(this).val();">
				@foreach ($schools as $sch)
			    	<option value="{{ $sch->o }}"{{ $dc == $sch->o ? ' selected' : '' }}>{{ $sch->description }}</option>
			    @endforeach
			</select>
			<select id="field" name="field" class="form-control" style="width: auto" onchange="if ($(this).val().substr(0,3) == 'ou=') location='{{ url()->current() }}?area=' + $('#area').val() + '&dc=' + $('#dc').val() + '&field=' + $(this).val();">
			    <option value="uuid" {{ $my_field == 'uuid' ? 'selected' : '' }}>使用者唯一編號</option>
			    <option value="idno" {{ $my_field == 'idno' ? 'selected' : '' }}>身分證字號</option>
			    <option value="name" {{ $my_field == 'name' ? 'selected' : '' }}>姓名</option>
			    <option value="mail" {{ $my_field == 'mail' ? 'selected' : '' }}>電子郵件</option>
			    <option value="mobile" {{ $my_field == 'mobile' ? 'selected' : '' }}>手機號碼</option>
				@foreach ($ous as $ou => $desc)
			    	<option value="ou={{ $ou }}" {{ $my_field == 'ou='.$ou ? 'selected' : '' }}>{{ $desc }}</option>
			    @endforeach
			    <option value="ou=empty" {{ $my_field == 'ou=empty' ? 'selected' : '' }}>待分類</option>
			    <option value="ou=deleted" {{ $my_field == 'ou=deleted' ? 'selected' : '' }}>已刪除</option>
			</select>
        	<input type="text" class="form-control" style="width:auto" id="keywords" name="keywords" value="{{ old('keywords') }}">
            <span class="input-group-btn" style="width: auto">
            	<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?area=' + $('#area').val() + '&dc=' + $('#dc').val() + '&field=' + $('#field').val() + '&keywords=' + $('#keywords').val();">
            		<i class="fa fa-search"></i>
            	</button>
        	</span>
    	</div>
	</div>
	<div class="col-sm-12">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>
				人員一覽表
			</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>狀態</th>
						<th>帳號</th>
						<th>身分證字號</th>
						<th>姓名</th>
						<th>身份</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@if ($people)
					@foreach ($people as $user)
					<tr title="{{ $user['entryUUID'] }}">
						<td style="vertical-align: inherit;">
							<span>{{ empty($user['inetUserStatus']) || $user['inetUserStatus'] == 'active' ? '啟用' : '' }}{{ $user['inetUserStatus'] == 'inactive' ? '停用' : '' }}{{ $user['inetUserStatus'] == 'deleted' ? '已刪除' : '' }}</span>
						</td>
						<td style="vertical-align: inherit;">
						@if (array_key_exists('uid', $user))
							@if (is_array($user['uid']))
							<span>{{ join(" ", $user['uid']) }}</span>
							@else
							<span>{{ $user['uid'] }}</span>
							@endif
						@else
							<span>無（請按回覆密碼）</span>
						@endif
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $user['cn'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>
							<span>{{ $user['displayName'] }}</span>
							</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ array_key_exists('employeeType', $user) ? $user['employeeType'] : '' }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<button type="button" class="btn btn-primary"
							 	onclick="$('#form').attr('action','{{ route('bureau.updatePeople', [ 'uuid' => $user['entryUUID'] ]) }}');
										 $('#form').attr('method', 'GET');
										 $('#form').submit();">編輯</button>
							@if ($user['inetUserStatus'] != 'deleted')
							<button type="button" class="btn btn-warning"
							 	onclick="$('#form').attr('action','{{ route('bureau.togglePeople', [ 'uuid' => $user['entryUUID'] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">{{ $user['inetUserStatus'] == 'inactive' ? '啟用' : '停用' }}</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('bureau.removePeople', [ 'uuid' => $user['entryUUID'] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">刪除</button>
							@else
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('bureau.undoPeople', [ 'uuid' => $user['entryUUID'] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">救回</button>
							@endif
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('bureau.resetpassPeople', [ 'uuid' => $user['entryUUID'] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">回復密碼</button>
						</td>
					</tr>
					@endforeach
					<form id="form" action="" method="" style="display: none;">
					@csrf
    				</form>
    				@endif
				</tbody>
			</table>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection
