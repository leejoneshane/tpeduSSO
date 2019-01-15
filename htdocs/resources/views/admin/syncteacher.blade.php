@extends('layouts.syncboard')

@section('page_heading')
同步教師（國小學程）
@endsection

@section('section')
<div class="container">
	<div class="row">
	@if ($result)
	    <div class="alert alert-info">
	    @foreach ($result as $line)
		{{ $line }}<br>
		@endforeach
	    </div>
	@endif
	<div class="col-sm-12">
		<form role="form" method="POST" action="{{ route('sync.ps.sync_teacher') }}">
		@csrf
    	<div class="input-group custom-search-form">
			<select name="area" class="form-control" style="width: auto" onchange="location='{{ url()->current() }}?area=' + $(this).val();">
				@foreach ($areas as $st)
			    	<option value="{{ $st }}"{{ $area == $st ? ' selected' : '' }}>{{ $st }}</option>
			    @endforeach
			</select>
			<select name="dc" class="form-control" style="width: auto">
				@foreach ($schools as $sch)
			    	<option value="{{ $sch->o }}"{{ $dc == $sch->o ? ' selected' : '' }}>{{ $sch->description }}</option>
			    @endforeach
			</select>
            <span class="input-group-btn" style="width: auto">
            	<button class="btn btn-default" type="submit">
            		開始同步
            	</button>
        	</span>
    	</div>
		</form>
	</div>
	</div>
</div>
@endsection
