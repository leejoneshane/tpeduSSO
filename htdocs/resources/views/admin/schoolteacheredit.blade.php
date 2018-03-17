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
					<label>任教班級</label>
					<input id="tclass" type="text" class="form-control" name="tclass" value="{{ isset($user) && array_key_exists('tpTeachClass', $user) ? (is_array($user['tpTeachClass']) ? implode(' ', $user['tpTeachClass']) : $user['tpTeachClass']) : '' }}"
					 placeholder="若任教多班，請在班級與班級之間使用半形空白加以區隔...">
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
					<label>電子郵件</label>
					<input id="mail" type="email" class="form-control" name="mail" value="{{ isset($user) && array_key_exists('mail', $user) ? (is_array($user['mail']) ? $user['mail'][0] : $user['mail']) : '' }}"
					 placeholder="用來傳送密碼重設連結，請務必填寫" required>
					@if ($errors->has('mail'))
						<p class="help-block">
							<strong>{{ $errors->first('mail') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
					<label>手機號碼</label>
					<input id="mobile" type="text" pattern="09[0-9]{8}" class="form-control" name="mobile" value="{{ isset($user) && array_key_exists('mobile', $user) ? (is_array($user['mobile']) ? $user['mobile'][0] : $user['mobile']) : '' }}"
					 placeholder="格式如右：0921000111">
					@if ($errors->has('mobile'))
						<p class="help-block">
							<strong>{{ $errors->first('mobile') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('fax') ? ' has-error' : '' }}">
					<label>傳真號碼</label>
					<input id="fax" type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" name="fax" value="{{ isset($user) && array_key_exists('fax', $user) ? (is_array($user['fax']) ? $user['fax'][0] : $user['fax']) : '' }}"
					 placeholder="格式如右：(02)23456789">
					@if ($errors->has('fax'))
						<p class="help-block">
							<strong>{{ $errors->first('fax') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('otel') ? ' has-error' : '' }}">
					<label>辦公電話</label>
					<input id="otel" type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" name="otel" value="{{ isset($user) && array_key_exists('telephoneNumber', $user) ? (is_array($user['telephoneNumber']) ? $user['telephoneNumber'][0] : $user['telephoneNumber']) : '' }}"
					 placeholder="格式如右：(02)23456789">
					@if ($errors->has('otel'))
						<p class="help-block">
							<strong>{{ $errors->first('otel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('htel') ? ' has-error' : '' }}">
					<label>住家電話</label>
					<input id="htel" type="text" pattern="^\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" name="htel" value="{{ isset($user) && array_key_exists('homePhone', $user) ? (is_array($user['homePhone']) ? $user['homePhone'][0] : $user['homePhone']) : '' }}"
					 placeholder="格式如右：(02)23456789">
					@if ($errors->has('htel'))
						<p class="help-block">
							<strong>{{ $errors->first('htel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('raddress') ? ' has-error' : '' }}">
					<label>戶籍地址</label>
					<input id="raddress" type="text" class="form-control" name="raddress" value="{{ isset($user) && array_key_exists('registeredAddress', $user) ? $user['registeredAddress'] : '' }}"
					 placeholder="請包含里鄰資訊...">
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
      			}
			</script>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection
