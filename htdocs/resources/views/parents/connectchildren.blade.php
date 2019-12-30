@extends('layouts.userboard')

@section('page_heading')
@endsection

@section('section')
<div class="container">
    <div class="row justify-content-center">
	<div class="col-md-8 col-md-offset-2">
	    <div class="card card-default" style="margin-top: 20px">
		<div class="card-header">親子連結服務</div>
		<div class="card-body">
		@if (session('error'))
		    <div class="alert alert-danger">
			{{ session('error') }}
		    </div>
		@endif
	
		@if (session('success'))
		    <div class="alert alert-success">
			{!! session('success') !!}
		    </div>
		@endif
		<p>親子連結關係之子女</p>
		<div class="col-md-10 col-md-offset-9">
			<a class="btn btn-primary" id='buttonAdd' name='buttonAdd' href='{{ route('parents.showConnectChildForm') }}'>新增親子連結</a>
		</div>		
		<form id="query" action="{{ route('bureau.usagerecord') }}" method="POST">
		<div class="row">
			<div class="panel-body">
				<table class="table table-hover" style="margin: 0;">
					<thead>
						<tr>
							<th>學號</th>
							<th>學生姓名</th>
							<th>就讀學校</th>
							<th>關係</th>
							<th>狀態</th>
						</tr>
					</thead>
					<tbody id="urtbody">
					@if (!empty($data))
					@foreach ($data as $d)
						<tr>
							<td style="vertical-align: inherit;">
								<label>{{ $d['student_id'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['student_name'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['school_name'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['parent_relation'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['status'] }}</label>
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