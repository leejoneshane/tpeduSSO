@extends('layouts.syncboard')

@section('page_heading')
<h1 class="page-header">移除標記為已刪除人員</h1>
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
