@extends('layouts.parent')

@section('page_heading')
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
				<div class="col-md-10 col-md-offset-9">
					<a class="btn btn-primary" id='buttonAdd' name='buttonAdd' href='{{ route('parents.showLinkForm') }}'>新增親子連結</a>
				</div>		
				<table class="table table-hover">
					<thead>
						<tr>
							<th>身分證字號</th>
							<th>學號</th>
							<th>學生姓名</th>
							<th>就讀學校</th>
							<th>就讀班級</th>
							<th>座號</th>
							<th>關係</th>
							<th>狀態</th>
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
								<button type="button" class="btn btn-danger"
									 onclick="$('#form').attr('action','{{ route('parent.removeLink', [ 'id' => $l->id ]) }}');
											 $('#form').attr('method', 'POST');
											 $('#form').submit();">刪除</button>
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