@extends('layouts.syncboard')

@section('page_heading')
虹橋校務行政系統資料連線測試中心
@endsection

@section('section')
<div class="container">
	<div class="row">
	@if (session('error'))
	    <div class="alert alert-danger">
		{{ session('error') }}
	    </div>
	@endif
	@if (session('success'))
	    <div class="alert alert-success">
		{{ session('success') }}
	    </div>
	@endif
	<div class="col-sm-6">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>參數設定</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
			<form role="form" method="POST" action="{{ route('sync.hs.runtime_test') }}" onsubmit="return check_all();">
		    	@csrf
				<label>資料項目：</label>
				<select id="field" name="field" class="form-control">
					<option value="schools_info" {{ $my_field == 'schools_info' ? 'selected' : '' }}>所有學校代號列表</option>
					<option value="school_info" {{ $my_field == 'school_info' ? 'selected' : '' }}>學校基本資料</option>
					<option value="units_info" {{ $my_field == 'units_info' ? 'selected' : '' }}>行政部門列表</option>
					<option value="classes_info" {{ $my_field == 'classes_info' ? 'selected' : '' }}>班級列表</option>
					<option value="subjects_info" {{ $my_field == 'subjects_info' ? 'selected' : '' }}>科目列表</option>
					<option value="teachers_info" {{ $my_field == 'teachers_info' ? 'selected' : '' }}>所有教師列表</option>
					<option value="roles_info" {{ $my_field == 'roles_info' ? 'selected' : '' }}>各部門職稱列表</option>
					<option value="teachers_in_class" {{ $my_field == 'teachers_in_class' ? 'selected' : '' }}>班級教師列表</option>
					<option value="students_in_class" {{ $my_field == 'students_in_class' ? 'selected' : '' }}>班級學生列表</option>
					<option value="person_info" {{ $my_field == 'person_info' ? 'selected' : '' }}>個人資料</option>
					<option value="person_change" {{ $my_field == 'person_change' ? 'selected' : '' }}>人員異動月報表</option>
				</select>
				<label>學校：</label>
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
				</div>
				<label>部門代號：</label>
				<input type="text" class="form-control" id="ou" name="ou" value="{{ $ou }}">
				<label>班級代號：</label>
				<input type="text" class="form-control" id="clsid" name="clsid" value="{{ $clsid }}">
				<label>身份證字號：</label>
				<input type="text" class="form-control" id="idno" name="idno" value="{{ $idno }}">
				<label>年月：</label>
				<input type="text" class="form-control" id="ym" name="ym" value="{{ $ym }}">
			    <div class="form-group">
					<button type="submit" class="btn btn-success">傳送請求</button>
				</div>
			</form>
			<script type="text/javascript">
				var fields = [];
				var msg = [];
      			function check_all() {
					field = $('#field').val();
					switch(field) {
						case 'roles_info':
							fields = "ou";
							break;
						case 'teachers_in_class':
						case 'students_in_class':
							fields = "clsid";
							break;
						case 'person_info':
							fields = "idno";
							break;
						case 'person_change':
							fields = "ym";
							break;
					}
					msg = [];
					if (fields == 'sid' && !$('#' + fields).val()) msg.push('學校');
					if (fields == 'ou' && !$('#' + fields).val()) msg.push('部門代號');
					if (fields == 'clsid' && !$('#' + fields).val()) msg.push('班級代號');
					if (fields == 'idno' && !$('#' + fields).val()) msg.push('身份證字號');
					if (fields == 'ym' && !$('#' + fields).val()) msg.push('年月');
					if (msg.length > 0) {
						alert('請務必輸入' + msg.join('、') + '!');
						return false;
					} else {
						return true;
					}
				};
			</script>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-6">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>測試結果</h4>
		</div>
		<div class="panel-body">
		@if ($result)
			<pre>{{ var_export($result, true) }}</pre>
		@endif
		</div>
		</div>
	</div>
	</div>
</div>
@endsection
