@extends('layouts.dashboard')

@section('page_heading')
<h1 class="page-header">同步學生</h1>
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
	@if (isset($clsid))
		<form id="sync" role="form" method="POST" action="{{ route('school.sync_student', [ 'dc' => $dc ]) }}">
			@csrf
			<input type="hidden" name="clsid" value="{{ $clsid }}">
			<span class="input-group-btn" style="width: auto">
				<button class="btn btn-default" type="submit">
					繼續同步下一個班級：{{ $clsid }}
				</button>
			</span>
		</form>
		<script>setTimeout("$('#sync').submit()", 60000);</script>
	@else
		<p>將從校務行政系統將學生資料同步到 openldap，同步過程中會自動將畢業或轉學學生標註為刪除，必要時會自動新增學生（新生入學）。</p>
		<p>此同步會自動更新學生個人資料，包含：姓名、性別、生日、學號、地址、電話、電子郵件、手機號碼和就讀年班、座號。</p>
		<p>如果學號相同，則保留原有帳號密碼。若學號不相同，則會將舊帳號刪除，重新建立預設帳號與密碼！</p>
		<p>每次僅同步一個班級，若顯示結果後管理員未回應網頁訊息，60秒後自動同步下一個班級。同步過程需要時間，直到全部同步完畢為止，請勿關閉瀏覽器或離開此網頁，以避免同步程序被關閉。</p>			
		<form role="form" method="POST" action="{{ route('school.sync_student', [ 'dc' => $dc ]) }}">
			@csrf
			<div class="col-sm-12">
				<div class="panel panel-default">	  
				<div class="panel-heading">
					<h4>請選擇要同步的班級：</h4>
				</div>
				<div class="panel-body">
					<div class="form-group">
						<input type="checkbox" id="all" name="all" value="all">全部班級
					</div>
					<div class="form-group">
						<select class="form-control" style="width:10%;display:inline" id="grade" name="grade">
							<option value="">同步年級</option>
						@if (!empty($grades))
						@foreach ($grades as $grade)
							<option value="{{ $grade }}">{{ $grade }}年級</option>
						@endforeach
						@endif
						</select>所有班級
					</div>
					<div class="form-group">
						<select class="form-control" style="width:20%;display:inline" id="class" name="class">
							<option value="">同步班級</option>
						@if (!empty($classes))
						@foreach ($classes as $cls)
							<option value="{{ $cls->ou }}">{{ $cls->description }}</option>
						@endforeach
						@endif
						</select>
					</div>
				</div>
				</div>
			</div>
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
