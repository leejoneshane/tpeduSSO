@extends('layouts.syncboard')

@section('page_heading')
國小學程資料連線測試中心
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
	<div class="col-sm-4">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>學校資訊</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
				<select id="field" name="field" class="form-control">
					<option value="school_info" {{ $my_field == 'school_info' ? 'selected' : '' }}>學校基本資料</option>
					<option value="department_info" {{ $my_field == 'department_info' ? 'selected' : '' }}>處室聯絡人資訊</option>
					<option value="classes_info" {{ $my_field == 'classes_info' ? 'selected' : '' }}>班級資訊</option>
					<option value="special_info" {{ $my_field == 'special_info' ? 'selected' : '' }}>特殊教育概況</option>
					<option value="calendar_info" {{ $my_field == 'calendar_info' ? 'selected' : '' }}>學校行事曆</option>
				</select>
				<label>學校統一編號：</label>
				<input type="text" class="form-control" id="sid" name="sid" value="{{ old('sid') }}" required>
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?scope=0&field=' + $('#field').val() + '&sid=' + $('#sid').val();">
						傳送請求
					</button>
				</span>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>年級資訊</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
				<select id="field" name="field" class="form-control">
					<option value="classses_by_grade" {{ $my_field == 'classses_by_grade' ? 'selected' : '' }}>各年級班級列表</option>
				</select>
				<label>學校統一編號：</label>
				<input type="text" class="form-control" id="sid" name="sid" value="{{ old('sid') }}" required>
				<label>年級：</label>
				<input type="text" class="form-control" id="grade" name="grade" value="{{ old('grade') }}" required>
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?scope=1&field=' + $('#field').val() + '&sid=' + $('#sid').val() + '&grade=' + $('#grade').val();">
						傳送請求
					</button>
				</span>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>科目資訊</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
				<select id="field" name="field" class="form-control">
					<option value="subject_info" {{ $my_field == 'subject_info' ? 'selected' : '' }}>科目資訊</option>
				</select>
				<label>學校統一編號：</label>
				<input type="text" class="form-control" id="sid" name="sid" value="{{ old('sid') }}" required>
				<label>科目代號：</label>
				<input type="text" class="form-control" id="subjid"" name="subjid" value="{{ old('subjid') }}" required>
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?scope=2&field=' + $('#field').val() + '&sid=' + $('#sid').val() + '&subjid=' + $('#subjid').val();">
						傳送請求
					</button>
				</span>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>班級資訊</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
				<select id="field" name="field" class="form-control">
					<option value="classs_info" {{ $my_field == 'classs_info' ? 'selected' : '' }}>班級基本資料</option>
					<option value="classs_schedule" {{ $my_field == 'classs_schedule' ? 'selected' : '' }}>班級課表</option>
					<option value="students_in_class" {{ $my_field == 'students_in_class' ? 'selected' : '' }}>班級學生列表</option>
					<option value="leaders_in_class" {{ $my_field == 'leaders_in_class' ? 'selected' : '' }}>班級幹部列表</option>
					<option value="teachers_in_class" {{ $my_field == 'teachers_in_class' ? 'selected' : '' }}>班級教師列表</option>
					<option value="subject_for_class" {{ $my_field == 'subject_for_class' ? 'selected' : '' }}>班級學科列表</option>
					<option value="class_lend_record" {{ $my_field == 'class_lend_record' ? 'selected' : '' }}>圖書借閱紀錄</option>
				</select>
				<label>學校統一編號：</label>
				<input type="text" class="form-control" id="sid" name="sid" value="{{ old('sid') }}" required>
				<label>班級代號：</label>
				<input type="text" class="form-control" id="clsid" name="clsid" value="{{ old('clsid') }}" required>
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?scope=3&field=' + $('#field').val() + '&sid=' + $('#sid').val() + '&clsid=' + $('#clsid').val();">
						傳送請求
					</button>
				</span>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>教師資訊</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
				<select id="field" name="field" class="form-control">
					<option value="teacher_info" {{ $my_field == 'teacher_info' ? 'selected' : '' }}>教師基本資料</option>
					<option value="teacher_schedule" {{ $my_field == 'teacher_schedule' ? 'selected' : '' }}>教師課表</option>
					<option value="teacher_tutor_students" {{ $my_field == 'teacher_tutor_students' ? 'selected' : '' }}>任教學生列表</option>
					<option value="subject_assign_to_teacher" {{ $my_field == 'subject_assign_to_teacher' ? 'selected' : '' }}>教師配課表</option>
				</select>
				<label>學校統一編號：</label>
				<input type="text" class="form-control" id="sid" name="sid" value="{{ old('sid') }}" required>
				<label>教師代號：</label>
				<input type="text" class="form-control" id="teaid" name="teaid" value="{{ old('teaid') }}" required>
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?scope=4&field=' + $('#field').val() + '&sid=' + $('#sid').val() + '&teaid=' + $('#teaid').val();">
						傳送請求
					</button>
				</span>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>學生資訊</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
				<select id="field" name="field" class="form-control">
					<option value="student_info" {{ $my_field == 'student_info' ? 'selected' : '' }}>學生基本資料</option>
					<option value="student_subjects_score" {{ $my_field == 'student_subjects_score' ? 'selected' : '' }}>學期各科成績</option>
					<option value="student_domains_score" {{ $my_field == 'student_domains_score' ? 'selected' : '' }}>學期領域成績</option>
					<option value="student_attendance_record" {{ $my_field == 'student_attendance_record' ? 'selected' : '' }}>學生出勤紀錄</option>
					<option value="student_health_record" {{ $my_field == 'student_health_record' ? 'selected' : '' }}>學生健康紀錄</option>
					<option value="student_parents_info" {{ $my_field == 'student_parents_info' ? 'selected' : '' }}>家長資訊</option>
				</select>
				<label>學校統一編號：</label>
				<input type="text" class="form-control" id="sid" name="sid" value="{{ old('sid') }}" required>
				<label>學生學號：</label>
				<input type="text" class="form-control" id="stdno" name="stdno" value="{{ old('stdno') }}" required>
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?scope=5&field=' + $('#field').val() + '&sid=' + $('#sid').val() + '&stdno=' + $('#stdno').val();">
						傳送請求
					</button>
				</span>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>圖書資訊</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
				<select id="field" name="field" class="form-control">
					<option value="library_books" {{ $my_field == 'library_books' ? 'selected' : '' }}>圖書統計</option>
					<option value="book_info" {{ $my_field == 'book_info' ? 'selected' : '' }}>圖書基本資料</option>
				</select>
				<label>學校統一編號：</label>
				<input type="text" class="form-control" id="sid" name="sid" value="{{ old('sid') }}" required>
				<label>ISBN：</label>
				<input type="text" class="form-control" id="isbn"" name="isbn" value="{{ old('isbn') }}" required>
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?scope=6&field=' + $('#field').val() + '&sid=' + $('#sid').val() + '&isbn=' + $('#isbn').val();">
						傳送請求
					</button>
				</span>
			</div>
		</div>
    	</div>
	</div>
	<div class="col-sm-12">
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
