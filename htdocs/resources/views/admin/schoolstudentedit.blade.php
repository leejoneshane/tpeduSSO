@extends('layouts.dashboard')

@section('page_heading')
{{ isset($user) ? '編輯' : '新增' }}學生資訊
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
	<div class="col-sm-8">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>{{ isset($user) ? '編輯' : '新增' }}學生資訊</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ isset($user) ? route('school.updateStudent', [ 'uuid' => $user['entryUUID'] ]) : route('school.createStudent') }}">
		    	@csrf
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
			    <div class="form-group{{ $errors->has('stdno') ? ' has-error' : '' }}">
					<label>學號</label>
					<input id="stdno" type="text" class="form-control" name="stdno" value="{{ isset($user) && array_key_exists('employeeNumber', $user) ? $user['employeeNumber'] : '' }}" required>
					@if ($errors->has('stdno'))
						<p class="help-block">
							<strong>{{ $errors->first('stdno') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('tclass') ? ' has-error' : '' }}">
					<label>就讀班級</label>
					<select id="tclass" type="text" class="form-control" name="tclass">
					@foreach ($ous as $ou => $desc)
			    		<option value="{{ $ou }}" {{ isset($user) && array_key_exists('tpClass', $user) && $ou == $user['tpClass'] ? 'selected' : '' }}>{{ $desc }}</option>
			    	@endforeach
					</select>
				</div>
			    <div class="form-group{{ $errors->has('seat') ? ' has-error' : '' }}">
					<label>座號</label>
					<input id="seat" type="text" class="form-control" name="seat" value="{{ isset($user) && array_key_exists('tpSeat', $user) ? $user['tpSeat'] : '' }}" required>
					@if ($errors->has('seat'))
						<p class="help-block">
							<strong>{{ $errors->first('seat') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('character') ? ' has-error' : '' }}">
					<label>特殊身份註記</label>
					<input id="character" type="text" class="form-control" name="character" value="{{ isset($user) && array_key_exists('tpCharacter', $user) ? $user['tpCharacter'] : '' }}"  
					placeholder="請用中文描述，例如：特殊生、清寒學生...，多重身份中間請使用半形空白隔開，無則省略。">
					@if ($errors->has('character'))
						<p class="help-block">
							<strong>{{ $errors->first('character') }}</strong>
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
					<input id="birth" type="date" class="form-control" name="birth" value="{{ isset($user) && array_key_exists('birthDate', $user) ? substr($user['birthDate'],0,4).'-'.substr($user['birthDate'],4,2).'-'.substr($user['birthDate'],6,2) : '' }}">
					@if ($errors->has('birth'))
						<p class="help-block">
							<strong>{{ $errors->first('birth') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mail') ? ' has-error' : '' }}">
					<label>電子郵件</label>
					<input id="mail" type="email" class="form-control" name="mail" value="{{ isset($user) && array_key_exists('mail', $user) ? $user['mail'] : '' }}" placeholder="用來傳送密碼重設連結，請務必填寫" required>
					@if ($errors->has('mail'))
						<p class="help-block">
							<strong>{{ $errors->first('mail') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
					<label>手機號碼</label>
					<input id="mobile" type="text" pattern="09[0-9]{8}" class="form-control" name="mobile" value="{{ isset($user) && array_key_exists('mobile', $user) ? $user['mobile'] : '' }}" placeholder="格式如右：0921000111">
					@if ($errors->has('mobile'))
						<p class="help-block">
							<strong>{{ $errors->first('mobile') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('fax') ? ' has-error' : '' }}">
					<label>傳真號碼</label>
					<input id="fax" type="text" pattern="^([0-9]{2,3})[0-9]{8}" class="form-control" name="fax" value="{{ isset($user) && array_key_exists('fax', $user) ? $user['fax'] : '' }}" placeholder="格式如右：(02)23456789">
					@if ($errors->has('fax'))
						<p class="help-block">
							<strong>{{ $errors->first('fax') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('otel') ? ' has-error' : '' }}">
					<label>辦公電話</label>
					<input id="otel" type="text" pattern="^([0-9]{2,3})[0-9]{8}" class="form-control" name="otel" value="{{ isset($user) && array_key_exists('telephoneNumber', $user) ? $user['telephoneNumber'] : '' }}" placeholder="格式如右：(02)23456789">
					@if ($errors->has('otel'))
						<p class="help-block">
							<strong>{{ $errors->first('otel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('htel') ? ' has-error' : '' }}">
					<label>住家電話</label>
					<input id="htel" type="text" pattern="^([0-9]{2,3})[0-9]{8}" class="form-control" name="htel" value="{{ isset($user) && array_key_exists('homePhone', $user) ? $user['homePhone'] : '' }}" placeholder="格式如右：(02)23456789">
					@if ($errors->has('htel'))
						<p class="help-block">
							<strong>{{ $errors->first('htel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('raddress') ? ' has-error' : '' }}">
					<label>戶籍地址</label>
					<input id="raddress" type="text" class="form-control" name="raddress" value="{{ isset($user) && array_key_exists('registeredAddress', $user) ? $user['registeredAddress'] : '' }}" placeholder="請包含里鄰資訊...">
					@if ($errors->has('raddress'))
						<p class="help-block">
							<strong>{{ $errors->first('raddress') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
					<label>郵寄地址</label>
					<input id="address" type="text" class="form-control" name="address" value="{{ isset($user) && array_key_exists('homePostalAddress', $user) ? $user['homePostalAddress'] : '' }}">
					@if ($errors->has('address'))
						<p class="help-block">
							<strong>{{ $errors->first('address') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('www') ? ' has-error' : '' }}">
					<label>個人首頁</label>
					<input id="www" type="text" class="form-control" name="www" value="{{ isset($user) && array_key_exists('wWWHomePage', $user) ? $user['wWWHomePage'] : '' }}">
					@if ($errors->has('www'))
						<p class="help-block">
							<strong>{{ $errors->first('www') }}</strong>
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
