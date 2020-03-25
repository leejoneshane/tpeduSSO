@extends('layouts.syncboard')

@section('page_heading')
轉移 Gsuite 帳號域名
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
	@if ($notfin)
	<form id="sync" role="form" method="GET" action="{{ route('sync.transfer_domain') }}">
		<span class="input-group-btn" style="width: auto">
			<button class="btn btn-primary" type="submit">
				繼續轉移
			</button>
		</span>
	</form>
	<script>setTimeout("$('#sync').submit()", 60000);</script>
	@endif
	</div>
</div>
@endsection
