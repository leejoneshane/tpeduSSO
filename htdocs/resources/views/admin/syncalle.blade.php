@extends('layouts.syncboard')

@section('page_heading')
全誼校務行政系統自動同步
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
	@else
		<p>即將進行自動化批次同步，同步作業將在背景執行，您可以離開頁面繼續操作，請使用 telescope 查看同步進度。</p>
		<form role="form" method="POST" action="{{ route('sync.ps.auto') }}">
			@csrf
			<div class="form-group">
				<button class="btn btn-default" type="submit" name="submit" value="true">
					我瞭解了，請開始同步
				</button>
			</div>
		</form>
	@endif
	</div>
</div>
@endsection
