@extends('layouts.syncboard')

@section('page_heading')
移除人員紀錄中的 Description 欄位
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
	</div>
</div>
@endsection
