@extends('layouts.dashboard')

@section('page_heading')
{{ isset($user) ? '編輯' : '新增' }}教師資訊
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
			<h4>{{ isset($user) ? '編輯' : '新增' }}教師資訊</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ isset($user) ? route('school.updateTeacher', [ 'uuid' => $user['entryUUID'] ]) : route('school.createTeacher') }}">
		    	@csrf
				<input type="hidden" name="my_field" value="{{ $my_field }}">
				<input type="hidden" name="keywords" value="{{ $keywords }}">
		    	@if (isset($user))
				<input type="hidden" name="uuid" value="{{ $user['entryUUID'] }}">
		    	@endif
			    <div class="form-group{{ $errors->has('idno') ? ' has-error' : '' }}">
					<label>身分證字號</label>
					<input id="idno" type="text" class="form-control" name="idno" value="{{ isset($user) ? $user['cn'] : '' }}" required>
					@if ($errors->has('idno'))
						<p class="help-block">
							<strong>{{ $errors->first('idno') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('sn') ? ' has-error' : '' }}">
					<label>姓氏</label>
					<input id="sn" type="text" class="form-control" name="sn" value="{{ isset($user) ? $user['sn'] : '' }}" required>
					@if ($errors->has('sn'))
						<p class="help-block">
							<strong>{{ $errors->first('sn') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('gn') ? ' has-error' : '' }}">
					<label>名字</label>
					<input id="gn" type="text" class="form-control" name="gn" value="{{ isset($user) ? $user['givenName'] : '' }}" required>
					@if ($errors->has('gn'))
						<p class="help-block">
							<strong>{{ $errors->first('gn') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<label>性別</label>
					<select id="gender" type="text" class="form-control" name="gender">
						<option value="0"{{ isset($user) && $user['gender'] == 0 ? ' selected' : '' }}>未知</option>
						<option value="1"{{ isset($user) && $user['gender'] == 1 ? ' selected' : '' }}>男</option>
						<option value="2"{{ isset($user) && $user['gender'] == 2 ? ' selected' : '' }}>女</option>
						<option value="9"{{ isset($user) && $user['gender'] == 9 ? ' selected' : '' }}>其它</option>
					</select>
				</div>
			    <div class="form-group{{ $errors->has('birth') ? ' has-error' : '' }}">
					<label>出生日期</label>
					<input id="birth" type="date" class="form-control" name="birth" value="{{ isset($user) ? substr($user['birthDate'],0,4).'-'.substr($user['birthDate'],4,2).'-'.substr($user['birthDate'],6,2) : '' }}">
					@if ($errors->has('birth'))
						<p class="help-block">
							<strong>{{ $errors->first('birth') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mail') ? ' has-error' : '' }}">
					<label>電子郵件</label>
					<input id="mail" type="email" class="form-control" name="mail" value="{{ isset($user) ? $user['mail'] : '' }}" placeholder="用來傳送密碼重設連結，請務必填寫" required>
					@if ($errors->has('mail'))
						<p class="help-block">
							<strong>{{ $errors->first('mail') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
					<label>手機號碼</label>
					<input id="mobile" type="text" pattern="09[0-9]{8}" class="form-control" name="mobile" value="{{ isset($user) ? $user['mobile'] : '' }}" placeholder="格式如右：0921000111">
					@if ($errors->has('mobile'))
						<p class="help-block">
							<strong>{{ $errors->first('mobile') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<button type="submit" class="btn btn-success">{{ isset($user) ? '變更' : '新增' }}</button>
				</div>
			</form>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection
