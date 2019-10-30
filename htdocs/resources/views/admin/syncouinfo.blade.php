@extends('layouts.dashboard')

@section('page_heading')
同步行政部門資訊
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
		<p>將從校務行政系統將行政部門資料同步到 openldap，同步過程中會自動移除已經不存在的單位，並更新單位名稱，同時也會視狀況為您新增行政部門。</p>
		<p>同步過程需要時間，直到結果出現為止，請勿關閉瀏覽器或離開此網頁，以避免同步程序被關閉。</p>
		<form role="form" method="POST" action="{{ route('school.sync_ou', [ 'dc' => $dc ]) }}">
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
