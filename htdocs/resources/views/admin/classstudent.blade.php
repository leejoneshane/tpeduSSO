@extends('layouts.tutorboard')

@section('page_heading')
學生管理
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
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>
				學生一覽表
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
						<th>班級代號</th>
						<th>座號</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
				@if ($students)
					@foreach ($students as $student)
					<tr title="{{ $student['entryUUID'] }}">
						<td style="vertical-align: inherit;">
							<span>{{ $student['inetUserStatus'] == 'active' ? '啟用' : '' }}{{ $student['inetUserStatus'] == 'inactive' ? '停用' : '' }}{{ $student['inetUserStatus'] == 'deleted' ? '已刪除' : '' }}</span>
						</td>
						<td style="vertical-align: inherit;">
						@if (array_key_exists('uid', $student))
							@if (is_array($student['uid']))
							<span>{{ join(" ", $student['uid']) }}</span>
							@else
							<span>{{ $student['uid'] }}</span>
							@endif
						@else
							<span>無（請按回覆密碼）</span>
						@endif
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $student['cn'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>
							<span>{{ $student['displayName'] }}</span>
							</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $student['tpClass'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $student['tpSeat'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<button type="button" class="btn btn-primary"
							 	onclick="$('#form').attr('action','{{ route('tutor.updateStudent', [ 'dc' => $dc, 'ou' => $ou, 'uuid' => $student['entryUUID'] ]) }}');
										 $('#form').attr('method', 'GET');
										 $('#form').submit();">編輯</button>
							@if ($student['inetUserStatus'] != 'deleted')
							<button type="button" class="btn btn-warning"
							 	onclick="$('#form').attr('action','{{ route('tutor.toggle', [ 'dc' => $dc, 'uuid' => $student['entryUUID'] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">{{ $student['inetUserStatus'] == 'inactive' ? '啟用' : '停用' }}</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('tutor.remove', [ 'dc' => $dc, 'uuid' => $student['entryUUID'] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">刪除</button>
							@else
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('tutor.undo', [ 'dc' => $dc, 'uuid' => $student['entryUUID'] ]) }}');
										 $('#form').attr('method', 'POST');
										 $('#form').submit();">救回</button>
							@endif
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('tutor.resetpass', [ 'dc' => $dc, 'uuid' => $student['entryUUID'] ]) }}');
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
