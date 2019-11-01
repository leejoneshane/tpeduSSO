@extends('layouts.syncboard')

@section('page_heading')
同步學生
@endsection

@section('section')
<div class="container">
	<div class="row">
	@if (isset($result))
	    <div class="alert alert-info">
	    @foreach ($result as $line)
		{{ $line }}<br>
		@endforeach
	    </div>
	@endif
	@if (isset($areas))
	<div class="col-sm-12">
		@if ($sims == 'alle')
		<form role="form" method="POST" action="{{ route('sync.ps.sync_student') }}">
		@endif
		@if ($sims == 'oneplus')
		<form role="form" method="POST" action="{{ route('sync.js.sync_student') }}">
		@endif
		@if ($sims == 'bridge')
		<form role="form" method="POST" action="{{ route('sync.hs.sync_student') }}">
		@endif
		@csrf
    	<div class="form-group">
			<select id='area' name='area' class="form-control" style="width: auto" onchange="location='{{ url()->current() }}?area=' + $(this).val();">
				@foreach ($areas as $st)
			    	<option value="{{ $st }}"{{ $area == $st ? ' selected' : '' }}>{{ $st }}</option>
			    @endforeach
			</select>
			<select name='dc' class="form-control" style="width: auto" onchange="location='{{ url()->current() }}?area=' + $('#area').val() + '&dc=' + $(this).val();">
				@foreach ($schools as $sch)
			    	<option value="{{ $sch->o }}"{{ $dc == $sch->o ? ' selected' : '' }}>{{ $sch->description }}</option>
			    @endforeach
			</select>
			<div class="form-group">
				<label>請選擇要同步的班級：</label>
				<div class="input-group">
					<input type="checkbox" id="all" name="all" value="all">全部班級
				</div>
				<div class="input-group">
					<select class="form-control" style="width:auto" id="grade" name="grade">
						<option value="">請選擇年級</option>
					@if (!empty($grades))
					@foreach ($grades as $grade)
						<option value="{{ $grade }}">{{ $grade }}年級</option>
					@endforeach
					@endif
					</select>
				</div>
				<div class="input-group">
					<select class="form-control" style="width:auto" id="class" name="class">
						<option value="">請選擇班級</option>
					@if (!empty($classes))
					@foreach ($classes as $cls)
						<option value="{{ $cls->ou }}">{{ $cls->description }}</option>
					@endforeach
					@endif
					</select>
				</div>
			</div>
			<span class="input-group-btn" style="width: auto">
            	<button class="btn btn-default" type="submit">
            		開始同步
            	</button>
        	</span>
    	</div>
		</form>
	</div>
	@else
	@if ($sims == 'alle')
	<form id="sync" role="form" method="POST" action="{{ route('sync.ps.sync_student') }}">
	@endif
	@if ($sims == 'oneplus')
	<form id="sync" role="form" method="POST" action="{{ route('sync.js.sync_student') }}">
	@endif
	@if ($sims == 'bridge')
	<form id="sync" role="form" method="POST" action="{{ route('sync.hs.sync_student') }}">
	@endif
		@csrf
		<input type="hidden" name="dc" value="{{ $dc }}">
		<input type="hidden" name="clsid" value="{{ $clsid }}">
		<span class="input-group-btn" style="width: auto">
			<button class="btn btn-default" type="submit">
				繼續同步下一個班級：{{ $clsid }}
			</button>
		</span>
		</form>
		<script>setTimeout("$('#sync').submit()", 10000);</script>
	@endif
	</div>
</div>
@endsection
