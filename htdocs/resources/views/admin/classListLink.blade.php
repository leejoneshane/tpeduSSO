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
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($links as $l)
					<tr>
						<td style="vertical-align: inherit;">
							<span>{{ $kids[$l->id]['idno'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $kids[$l->id]['stdno'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $kids[$l->id]['name'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $kids[$l->id]['school'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $kids[$l->id]['class'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $kids[$l->id]['seat'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $l->relation }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ ($l->verified) ? '作用中' : '無作用' }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<form action="{{ route('parent.removeLink', [ 'id' => $l->id ]) }}" method="POST">
								@csrf
								<input type="submit" class="btn btn-danger" value="刪除">
							</form>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection