@extends('layouts.dashboard', [ 'category' => $category, 'dc' => $dc ])

@section('page_heading')
同步班級資訊
@endsection

@section('section')
<div class="container">
	<div class="row">
	<div class="col-sm-12">
	@if (isset($result))
		@foreach ($result as $line)
		{{ $line }}<br>
		@endforeach
	@else
		<p>將從校務行政系統將班級資料同步到 openldap，同步過程中會自動移除已經不存在的班級，並更新班級名稱，同時也會視狀況為您新增班級。</p>
		<p>同步過程需要時間，直到結果出現為止，請勿關閉瀏覽器或離開此網頁，以避免同步程序被關閉。</p>
		<div class="input-group custom-search-form">
            <span class="input-group-btn" style="width: auto">
            	<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?submit=true';">
            		我瞭解了，請開始同步
            	</button>
        	</span>
    	</div>
	@endif
	</div>
	</div>
</div>
@endsection