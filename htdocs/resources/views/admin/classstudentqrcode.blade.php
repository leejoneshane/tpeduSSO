@extends('layouts.tutorboard')

@section('page_heading')
學生 QRCODE 管理
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
				學生 QRCODE 一覽表
			</h4>
			<div class="col-md-10 col-md-offset-9">
				<button class="btn btn-primary" onclick="window.print();">列印</button>
			</div>		
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>姓名</th>
						<th>座號</th>
						<th>QRCODE</th>
						<th>到期日</th>
					</tr>
				</thead>
				<tbody>
				@if ($students)
					@foreach ($students as $student)
					<tr title="{{ $student['entryUUID'] }}">
						<td style="vertical-align: inherit;">
							<span>
							<span>{{ $student['displayName'] }}</span>
							</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $student['tpSeat'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{!! isset($student['QRCODE']) ? $student['QRCODE'] : '尚未產生' !!}</span>
						</td>
						<td style="vertical-align: inherit;">
							<button type="button" class="btn btn-primary"
							 	onclick="$('#form').attr('action','{{ route('tutor.generateQrcode', [ 'dc' => $dc, 'ou' => $ou, 'uuid' => $student['entryUUID'] ]) }}');
										 $('#form').submit();">重新產生</button>
							@if ($student['QRCODE'])
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('tutor.removeQrcode', [ 'dc' => $dc, 'ou' => $ou, 'uuid' => $student['entryUUID'] ]) }}');
										 $('#form').submit();">刪除 QRCODE</button>
							@endif
						</td>
					</tr>
					@endforeach
					<form id="form" action="" method="POST" style="display: none;">
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
