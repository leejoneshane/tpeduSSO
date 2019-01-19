@extends('layouts.dashboard', [ 'category' => $category, 'dc' => $dc ])

@section('page_heading')
同步教師
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
		<p>將從校務行政系統將教師資料同步到 openldap，同步過程中會自動修正教師個人資料、設定單位職稱、級任、配課資訊，並將離職教師標註為刪除，但無法處理帳號密碼同步。</p>
		<p>同步過程需要時間，直到結果出現為止，請勿關閉瀏覽器或離開此網頁，以避免同步程序被關閉。</p>			
		@if ($category == '國民小學' || $category == '幼兒園')
		<form role="form" method="POST" action="{{ route('school.ps.sync_teacher', [ 'dc' => $dc ]) }}">
		@endif
		@if ($category == '國民中學' || $category == '高中')
		<form role="form" method="POST" action="{{ route('school.js.sync_teacher', [ 'dc' => $dc ]) }}">
		@endif
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
