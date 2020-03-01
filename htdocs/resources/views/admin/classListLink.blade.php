@extends('layouts.tutorboard')

@section('page_heading')
審核親子連結
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
				親子連結一覽表
			</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>申請人</th>
						<th>身分證字號</th>
						<th>電子郵件</th>
						<th>手機號碼</th>
						<th>學生姓名</th>
						<th>座號</th>
						<th>關係</th>
						<th>自動檢測結果</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($links as $l)
					<tr>
						<td style="vertical-align: inherit;">
							<span>{{ $records[$l->id]['parent'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $l->parent_idno }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $records[$l->id]['email'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $records[$l->id]['mobile'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $records[$l->id]['student'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $records[$l->id]['seat'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $l->relation }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $l->denyReason }}</span>
						</td>
						<td style="vertical-align: inherit;">
							@if ($l->verified)
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('tutor.denyLink', [ 'id' => $l->id ]) }}');
										 $('#form').submit();">拒絕</button>
							@else
							<button type="button" class="btn btn-success"
							 	onclick="$('#form').attr('action','{{ route('tutor.verifyLink', [ 'id' => $l->id ]) }}');
										 $('#form').submit();">同意</button>
							@endif
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		</div>
		<form id="form" action="" method="POST" style="display: none;">
		@csrf
		</form>
	</div>
	</div>
</div>
@endsection