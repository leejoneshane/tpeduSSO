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
	<div class="col-sm-8">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>{{ isset($user) ? '編輯' : '新增' }}教師資訊</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ isset($user) ? route('school.updateTeacher', [ 'uuid' => $user['entryUUID'] ]) : route('school.createTeacher') }}">
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
			    <div class="form-group">
					<label>隸屬單位</label>
					<select id="ou" type="text" class="form-control" name="ou" onchange="refresh_roles()">
					@foreach ($ous as $ou => $desc)
			    		<option value="{{ $ou }}" {{ isset($user) && array_key_exists('ou', $user) && $ou == $user['ou'] ? 'selected' : '' }}>{{ $desc }}</option>
			    	@endforeach
					</select>
				</div>
			    <div class="form-group">
					<label>主要職稱</label>
					<select id="role" type="text" class="form-control" name="role">
					@foreach ($roles as $role => $desc)
			    		<option value="{{ $role }}" {{ isset($user) && array_key_exists('title', $user) && $role == $user['title'] ? 'selected' : '' }}>{{ $desc }}</option>
			    	@endforeach
					</select>
				</div>
			    <div class="form-group{{ $errors->has('tclass') ? ' has-error' : '' }}">
					<label style="display:block">任教班級</label>
					@if (isset($user) && array_key_exists('tpTeachClass', $user))
						@if (is_array($user['tpTeachClass']))
							@foreach ($user['tpTeachClass'] as $class)
							<input type="text" class="form-control" style="width:50%;display:inline" name="tclass[]" value="{{ $class }}" placeholder="請輸入班級代號"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" class="form-control" style="width:50%;display:inline" name="tclass[]" value="{{ $user['tpTeachClass'] }}" placeholder="請輸入班級代號"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endif
					@endif
						<button id="nclass" type="button" class="btn btn-primary btn-circle" onclick="add_tclass()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('tclass'))
						<p class="help-block">
							<strong>{{ $errors->first('tclass') }}</strong>
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
					<label style="display:block">電子郵件</label>
					@if (isset($user) && array_key_exists('mail', $user))
						@if (is_array($user['mail']))
							@foreach ($user['mail'] as $mail)
							<input type="text" class="form-control" style="width:50%;display:inline" name="mail[]" value="{{ $mail }}" placeholder="用來傳送密碼重設連結，請務必填寫" required><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" class="form-control" style="width:50%;display:inline" name="mail[]" value="{{ $user['mail'] }}" placeholder="用來傳送密碼重設連結，請務必填寫" required><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endif
					@endif
						<button id="nmail" type="button" class="btn btn-primary btn-circle" onclick="add_mail()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('mail'))
						<p class="help-block">
							<strong>{{ $errors->first('mail') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
					<label style="display:block">手機號碼</label>
					@if (isset($user) && array_key_exists('mobile', $user))
						@if (is_array($user['mobile']))
							@foreach ($user['mobile'] as $mobile)
							<input type="text" pattern="09[0-9]{8}" class="form-control" style="width:50%;display:inline" name="mobile[]" value="{{ $mobile }}" placeholder="格式如右：0921000111"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" pattern="09[0-9]{8}" class="form-control" style="width:50%;display:inline" name="mobile[]" value="{{ $user['mobile'] }}" placeholder="格式如右：0921000111"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endif
					@endif
						<button id="nmobile" type="button" class="btn btn-primary btn-circle" onclick="add_mobile()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('mobile'))
						<p class="help-block">
							<strong>{{ $errors->first('mobile') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('fax') ? ' has-error' : '' }}">
					<label style="display:block">傳真號碼</label>
					@if (isset($user) && array_key_exists('fax', $user))
						@if (is_array($user['fax']))
							@foreach ($user['fax'] as $fax)
							<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="fax[]" value="{{ $fax }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="fax[]" value="{{ $user['fax'] }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endif
					@endif
						<button id="nfax" type="button" class="btn btn-primary btn-circle" onclick="add_fax()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('fax'))
						<p class="help-block">
							<strong>{{ $errors->first('fax') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('otel') ? ' has-error' : '' }}">
					<label style="display:block">辦公電話</label>
					@if (isset($user) && array_key_exists('otel', $user))
						@if (is_array($user['otel']))
							@foreach ($user['otel'] as $otel)
							<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="otel[]" value="{{ $otel }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="otel[]" value="{{ $user['otel'] }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endif
					@endif
						<button id="notel" type="button" class="btn btn-primary btn-circle" onclick="add_otel()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('otel'))
						<p class="help-block">
							<strong>{{ $errors->first('otel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('htel') ? ' has-error' : '' }}">
					<label style="display:block">住家電話</label>
					@if (isset($user) && array_key_exists('htel', $user))
						@if (is_array($user['htel']))
							@foreach ($user['htel'] as $htel)
							<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="htel[]" value="{{ $htel }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="htel[]" value="{{ $user['htel'] }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endif
					@endif
						<button id="nhtel" type="button" class="btn btn-primary btn-circle" onclick="add_htel()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('htel'))
						<p class="help-block">
							<strong>{{ $errors->first('htel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('raddress') ? ' has-error' : '' }}">
					<label style="display:block">戶籍地址</label>
					<input id="raddress" type="text" class="form-control" name="raddress" value="{{ isset($user) && array_key_exists('registeredAddress', $user) ? $user['registeredAddress'] : '' }}"
					 placeholder="請包含里鄰資訊...">
					@if ($errors->has('raddress'))
						<p class="help-block">
							<strong>{{ $errors->first('raddress') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
					<label style="display:block">郵寄地址</label>
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
			<script type="text/javascript">
				window.axios.defaults.headers.common = {
					'X-Requested-With': 'XMLHttpRequest',
				};
				function refresh_roles() {
					axios.get('/school/roles/{{ $dc }}/' + $('#ou').val())
    					.then(response => {
    						$('#role').find('option').remove();
        					response.data.forEach(
        						function add_option(role) { $('#role').append('<option value="' + role.cn + '">' + role.description + '</option>'); }
        					);
    					})
						.catch(function (error) {
							console.log(error);
  						});
      			};
      			
      			function add_tclass() {
					$('#nclass').before('<input type="text" class="form-control" style="width:50%;display:inline" name="tclass[]" placeholder="請輸入班級代號">');
					$('#nclass').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_mail() {
					$('#nmail').before('<input type="text" class="form-control" style="width:50%;display:inline" name="mail[]" placeholder="用來傳送密碼重設連結，請務必填寫" required>');
					$('#nmail').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_mobile() {
					$('#nmobile').before('<input type="text" pattern="09[0-9]{8}" class="form-control" style="width:50%;display:inline" name="mobile[]" placeholder="格式如右：0921000111">');
					$('#nmobile').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_fax() {
					$('#nfax').before('<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="fax[]" placeholder="格式如右：(02)23456789">');
					$('#nfax').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_otel() {
					$('#notel').before('<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="otel[]" placeholder="格式如右：(02)23456789">');
					$('#notel').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_htel() {
					$('#nhtel').before('<input type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="htel[]" placeholder="格式如右：(02)23456789">');
					$('#nhtel').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};
			</script>
		</div>
	</div>
	</div>
</div>
@endsection