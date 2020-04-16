@extends('layouts.dashboard')

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
    	<div class="input-group custom-search-form">
			<select id="field" name="field" class="form-control" style="flex: none; width: auto" onchange="location='{{ url()->current() }}?field=' + $(this).val();">
				@foreach ($classes as $ou => $desc)
			    	<option value="{{ $ou }}" {{ $my_field == $ou ? 'selected' : '' }}>{{ $desc }}</option>
			    @endforeach
			</select>
            <span class="input-group-btn" style="width: auto">
            	<button class="btn btn-default" type="button">
            		<i class="fa fa-search"></i>
            	</button>
        	</span>
    	</div>
	</div>
	<div class="col-sm-12">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>
				學生 QRCODE 一覽表
			</h4>
			<div class="col-md-10">
				<button class="btn btn-success float-right" onclick="window.print();">列印</button>
			</div>		
		</div>
		<div class="panel-body">
			<p style="color:red">使用學生 Qrcode 注意事項：
				<ul>
					<li>學生 Qrcode 僅用於確認家長與學生之親子關係。如家長帳號已經與學生帳號綁定關係，則無須再次產生學生 Qrcode。</li>
					<li>為避免爭議，請導師務必利用學校日或其他公開場合面交學生 Qrcode 給家長，以免親子關係遭到冒名頂替。</li>
					<li>家長使用學生 Qrcode 之前仍須先註冊家長帳號並進行登入，然後再掃描學生 Qrcode 始能發揮作用。</li>
					<li>每一個 Qrcode 僅能供一個家長帳號綁定一個學生，使用後會立即刪除。</li>
					<li>學生 Qrcode 必須在五天以內使用，超過時效即自動作廢。</li>
					<li>如遇有多位家長想綁定同一學生，須再次產生新的 Qrcode，每位學生同時只能產生一個 Qrcode。</li>
				</ul>
			</p>
			<hr>
			<table class="table table-hover">
				<thead>
					<tr>
						<th>姓名</th>
						<th>座號</th>
						<th>QRCODE</th>
						<th>到期日</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				@if ($students)
					@foreach ($students as $student)
					<tr>
						<td style="vertical-align: inherit;">
							<span>
							<span>{{ $student['displayName'] }}</span>
							</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $student['tpSeat'] }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{!! isset($student['QRCODE'])  ? $student['QRCODE'] : '尚未產生' !!}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ isset($student['expired'])  ? $student['expired'] : '-' }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<button type="button" class="btn btn-primary"
							 	onclick="$('#form').attr('action','{{ route('school.generateQrcode', [ 'uuid' => $student['entryUUID'] ]) }}');
										 $('#form').submit();">重新產生</button>
							@if (isset($student['QRCODE']))
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('school.removeQrcode', [ 'uuid' => $student['entryUUID'] ]) }}');
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
