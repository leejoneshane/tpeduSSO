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
	<div class="col-sm-6">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>參數設定</h4>
		</div>
		<div class="panel-body">
			<div class="form-group custom-search-form">
			<form role="form" method="POST" action="{{ route('sync.ps.runtime_test') }}" onsubmit="return check_all();">
		    	@csrf
				<label>資料項目：</label>
				<select id="field" name="field" class="form-control">
					<option value="school_info" {{ $my_field == 'school_info' ? 'selected' : '' }}>學校基本資料</option>
					<option value="teachers_info" {{ $my_field == 'teachers_info' ? 'selected' : '' }}>全校教職員列表</option>
					<option value="department_info" {{ $my_field == 'department_info' ? 'selected' : '' }}>處室聯絡人資訊</option>
					<option value="classes_info" {{ $my_field == 'classes_info' ? 'selected' : '' }}>班級資訊</option>
					<option value="special_info" {{ $my_field == 'special_info' ? 'selected' : '' }}>特殊教育概況</option>
					<option value="calendar_info" {{ $my_field == 'calendar_info' ? 'selected' : '' }}>學校行事曆</option>
					<option value="classses_by_grade" {{ $my_field == 'classses_by_grade' ? 'selected' : '' }}>各年級班級列表</option>
					<option value="subject_info" {{ $my_field == 'subject_info' ? 'selected' : '' }}>科目資訊</option>
					<option value="classs_info" {{ $my_field == 'classs_info' ? 'selected' : '' }}>班級基本資料</option>
					<option value="classs_schedule" {{ $my_field == 'classs_schedule' ? 'selected' : '' }}>班級課表</option>
					<option value="students_in_class" {{ $my_field == 'students_in_class' ? 'selected' : '' }}>班級學生列表</option>
					<option value="leaders_in_class" {{ $my_field == 'leaders_in_class' ? 'selected' : '' }}>班級幹部列表</option>
					<option value="teachers_in_class" {{ $my_field == 'teachers_in_class' ? 'selected' : '' }}>班級教師列表</option>
					<option value="subject_for_class" {{ $my_field == 'subject_for_class' ? 'selected' : '' }}>班級學科列表</option>
					<option value="class_lend_record" {{ $my_field == 'class_lend_record' ? 'selected' : '' }}>圖書借閱紀錄</option>
					<option value="teacher_info" {{ $my_field == 'teacher_info' ? 'selected' : '' }}>教師基本資料</option>
					<option value="teacher_detail" {{ $my_field == 'teacher_detail' ? 'selected' : '' }}>教師個人資料</option>
					<option value="teacher_schedule" {{ $my_field == 'teacher_schedule' ? 'selected' : '' }}>教師課表</option>
					<option value="teacher_tutor_students" {{ $my_field == 'teacher_tutor_students' ? 'selected' : '' }}>任教學生列表</option>
					<option value="subject_assign_to_teacher" {{ $my_field == 'subject_assign_to_teacher' ? 'selected' : '' }}>教師配課表</option>
					<option value="student_info" {{ $my_field == 'student_info' ? 'selected' : '' }}>學生基本資料</option>
					<option value="student_detail" {{ $my_field == 'student_detail' ? 'selected' : '' }}>學生個人資料</option>
					<option value="student_subjects_score" {{ $my_field == 'student_subjects_score' ? 'selected' : '' }}>學期各科成績</option>
					<option value="student_domains_score" {{ $my_field == 'student_domains_score' ? 'selected' : '' }}>學期領域成績</option>
					<option value="student_attendance_record" {{ $my_field == 'student_attendance_record' ? 'selected' : '' }}>學生出勤紀錄</option>
					<option value="student_health_record" {{ $my_field == 'student_health_record' ? 'selected' : '' }}>學生健康紀錄</option>
					<option value="student_parents_info" {{ $my_field == 'student_parents_info' ? 'selected' : '' }}>家長資訊</option>
					<option value="library_books" {{ $my_field == 'library_books' ? 'selected' : '' }}>圖書統計</option>
					<option value="book_info" {{ $my_field == 'book_info' ? 'selected' : '' }}>圖書基本資料</option>
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
				<label>年級：</label>
				<input type="text" class="form-control" id="grade" name="grade" value="{{ $grade }}">
				<label>科目代號：</label>
				<input type="text" class="form-control" id="subjid" name="subjid" value="{{ $subjid }}">
				<label>班級代號：</label>
				<input type="text" class="form-control" id="clsid" name="clsid" value="{{ $clsid }}">
				<label>教師代號：</label>
				<input type="text" class="form-control" id="teaid" name="teaid" value="{{ $teaid }}">
				<label>學生學號：</label>
				<input type="text" class="form-control" id="stdno" name="stdno" value="{{ $stdno }}">
				<label>ISBN：</label>
				<input type="text" class="form-control" id="isbn" name="isbn" value="{{ $isbn }}">
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
						case 'school_info':
						case 'department_info':
						case 'classes_info':
						case 'special_info':
						case 'calendar_info':
						case 'library_books':
						case 'teachers_info':
							fields = ["sid"];
							break;
						case 'classses_by_grade':
							fields = ["sid", "grade"];
							break;
						case 'subject_info':
							fields = ["sid", "subjid"];
							break;
						case 'classs_info':
						case 'classs_schedule':
						case 'students_in_class':
						case 'leaders_in_class':
						case 'teachers_in_class':
						case 'subject_for_class':
						case 'class_lend_record':
							fields = ["sid", "clsid"];
							break;
						case 'teacher_info':
						case 'teacher_detail':
						case 'teacher_schedule':
						case 'teacher_tutor_students':
						case 'subject_assign_to_teacher':
							fields = ["sid", "teaid"];
							break;
						case 'student_info':
						case 'student_detail':
						case 'student_subjects_score':
						case 'student_domains_score':
						case 'student_attendance_record':
						case 'student_health_record':
						case 'student_parents_info':
							fields = ["sid", "stdno"];
							break;
						case 'book_info':
							fields = ["sid", "isbn"];
							break;
					}
					msg = [];
					for(i=0;i<fields.length;i++) {
						if (fields[i] == 'sid' && !$('#' + fields[i]).val()) msg.push('學校');
						if (fields[i] == 'grade' && !$('#' + fields[i]).val()) msg.push('年級');
						if (fields[i] == 'subjid' && !$('#' + fields[i]).val()) msg.push('科目代號');
						if (fields[i] == 'clsid' && !$('#' + fields[i]).val()) msg.push('班級代號');
						if (fields[i] == 'teaid' && !$('#' + fields[i]).val()) msg.push('教師代號');
						if (fields[i] == 'stdno' && !$('#' + fields[i]).val()) msg.push('學生學號');
						if (fields[i] == 'isbn' && !$('#' + fields[i]).val()) msg.push('書號(ISBN)');
					}
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
