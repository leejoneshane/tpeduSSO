@extends('layouts.dashboard')

@section('page_heading')
{{ isset($user) ? '編輯' : '新增' }}教師資訊
@endsection

@section('section')
<div class="container">
	<div class="row">
	@if (empty($roles))
		<div class="alert alert-danger">
		因為尚未設定行政部門與職稱，因此您無法新增或修改教師資訊！
		</div>
	@endif
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
		@if (!empty($roles))
		<div class="panel-body">
			<form role="form" method="POST" action="{{ isset($user) ? route('school.updateTeacher', [ 'dc' => $dc, 'uuid' => $user['entryUUID'] ]) : route('school.createTeacher', [ 'dc' => $dc ]) }}">
		    	@csrf
		    	@if (isset($user))
				<input type="hidden" name="uuid" value="{{ $user['entryUUID'] }}">
		    	@endif
			    <div class="form-group{{ $errors->has('idno') ? ' has-error' : '' }}">
					<label>身分證字號</label>
					<input id="idno" type="text" class="form-control" name="idno" value="{{ !empty($user['cn']) ? $user['cn'] : '' }}" required{{ isset($user) ? ' readonly' : ''}}>
					@if ($errors->has('idno'))
						<p class="help-block">
							<strong>{{ $errors->first('idno') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('sn') ? ' has-error' : '' }}">
					<label>姓氏</label>
					<input id="sn" type="text" class="form-control" name="sn" value="{{ !empty($user['sn']) ? $user['sn'] : '' }}" required>
					@if ($errors->has('sn'))
						<p class="help-block">
							<strong>{{ $errors->first('sn') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('gn') ? ' has-error' : '' }}">
					<label>名字</label>
					<input id="gn" type="text" class="form-control" name="gn" value="{{ !empty($user['givenName']) ? $user['givenName'] : '' }}" required>
					@if ($errors->has('gn'))
						<p class="help-block">
							<strong>{{ $errors->first('gn') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<label>身份別</label>
					<select id="type" class="form-control" name="type" onchange="switchtype()">
					@foreach ($types as $type)
			    		<option value="{{ $type }}"{{ !empty($user['employeeType']) && $type == $user['employeeType'] ? ' selected' : '' }}>{{ $type }}</option>
			    	@endforeach
					</select>
				</div>
			    <div class="form-group">
					<label style="display:block">單位職稱</label>
					@if (!empty($user['title']))
					@if (is_array($user['title']))
						@foreach ($user['title'] as $title)
						<div></div>
						<select id="role" class="form-control" style="width:50%;display:inline" name="roles[]">
							@foreach ($roles as $role => $desc)
			    			<option value="{{ $role }}"{{ $title == $dc.','.$role  ? ' selected' : '' }}>{{ $desc }}</option>
			    		@endforeach
						</select><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endforeach
					@else
					<select id="role" class="form-control" style="width:50%;display:inline" name="roles[]">
							@foreach ($roles as $role => $desc)
							<option value="{{ $role }}"{{ $user['title'] == $dc.','.$role  ? ' selected' : '' }}>{{ $desc }}</option>
							@endforeach
					</select><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
					@endif
					@endif
					<button id="nrole" type="button" class="btn btn-primary btn-circle" onclick="add_role()"><i class="fa fa-plus"></i></button>
				</div>
			    <div class="form-group{{ $errors->has('tclass') ? ' has-error' : '' }}">
					<label style="display:block">配課資訊</label>
					@if (!empty($assign))
						@foreach ($assign as $pair)
						<div></div>
						<select class="form-control" style="width:25%;display:inline" name="tclass[]">
							@foreach ($classes as $ou => $desc)
				    		<option value="{{ $ou }}"{{ isset($pair['class']) && $pair['class'] == $ou ? ' selected' : '' }}>{{ $desc }}</option>
				    	@endforeach
						</select><select class="form-control" style="width:25%;display:inline" name="subj[]">
							@foreach ($subjects as $id => $name)
				    		<option value="{{ $id }}"{{ isset($pair['subject']) && $pair['subject'] == $id ? ' selected' : '' }}>{{ $name }}</option>
				    	@endforeach
						</select><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().prev().remove();$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endforeach
					@endif
						<button id="nclass" type="button" class="btn btn-primary btn-circle" onclick="add_tclass()"><i class="fa fa-plus"></i></button>
				</div>
			    <div class="form-group{{ $errors->has('character') ? ' has-error' : '' }}">
					<label style="display:block">特殊身份註記</label>
					@if (!empty($user['tpCharacter']))
						@if (is_array($user['tpCharacter']))
							@foreach ($user['tpCharacter'] as $character)
							<input type="text" class="form-control" style="width:50%;display:inline" name="character[]" value="{{ $character }}" placeholder="請用中文描述，例如：巡迴教師、均一平台管理員...，無則省略。" required><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" class="form-control" style="width:50%;display:inline" name="character[]" value="{{ $user['tpCharacter'] }}" placeholder="請用中文描述，例如：巡迴教師、均一平台管理員...，無則省略。" required><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
						@endif
					@endif
						<button id="ncharacter" type="button" class="btn btn-primary btn-circle" onclick="add_character()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('character'))
						<p class="help-block">
							<strong>{{ $errors->first('character') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<label>性別</label>
					<select id="gender" class="form-control" name="gender">
						<option value="0"{{ empty($user['gender']) || $user['gender'] == 0 ? ' selected' : '' }}>未知</option>
						<option value="1"{{ !empty($user['gender']) && $user['gender'] == 1 ? ' selected' : '' }}>男</option>
						<option value="2"{{ !empty($user['gender']) && $user['gender'] == 2 ? ' selected' : '' }}>女</option>
						<option value="9"{{ !empty($user['gender']) && $user['gender'] == 9 ? ' selected' : '' }}>其它</option>
					</select>
				</div>
			    <div class="form-group{{ $errors->has('birth') ? ' has-error' : '' }}">
					<label>出生日期</label>
					<input id="birth" type="date" class="form-control" name="birth" value="{{ !empty($user['birthDate']) ? substr($user['birthDate'],0,4).'-'.substr($user['birthDate'],4,2).'-'.substr($user['birthDate'],6,2) : '' }}">
					@if ($errors->has('birth'))
						<p class="help-block">
							<strong>{{ $errors->first('birth') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mail') ? ' has-error' : '' }}">
					<label style="display:block">電子郵件（只有主要電子郵件可用於登入和重設密碼）</label>
					@if (!empty($user['mail']))
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
					<label style="display:block">手機號碼（只有主要手機號碼可用於登入和重設密碼）</label>
					@if (!empty($user['mobile']))
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
					@if (!empty($user['fax']))
						@if (is_array($user['fax']))
							@foreach ($user['fax'] as $fax)
							<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="fax[]" value="{{ $fax }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="fax[]" value="{{ $user['fax'] }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
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
					@if (!empty($user['otel']))
						@if (is_array($user['otel']))
							@foreach ($user['otel'] as $otel)
							<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="otel[]" value="{{ $otel }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="otel[]" value="{{ $user['otel'] }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
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
					@if (!empty($user['htel']))
						@if (is_array($user['htel']))
							@foreach ($user['htel'] as $htel)
							<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="htel[]" value="{{ $htel }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endforeach
						@else
							<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="htel[]" value="{{ $user['htel'] }}" placeholder="格式如右：(02)23456789"><button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
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
					<input id="raddress" type="text" class="form-control" name="raddress" value="{{ !empty($user['registeredAddress']) ? $user['registeredAddress'] : '' }}"
					 placeholder="請包含里鄰資訊...">
					@if ($errors->has('raddress'))
						<p class="help-block">
							<strong>{{ $errors->first('raddress') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
					<label style="display:block">郵寄地址</label>
					<input id="address" type="text" class="form-control" name="address" value="{{ !empty($user['homePostalAddress']) ? $user['homePostalAddress'] : '' }}">
					@if ($errors->has('address'))
						<p class="help-block">
							<strong>{{ $errors->first('address') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('www') ? ' has-error' : '' }}">
					<label>個人首頁</label>
					<input id="www" type="text" class="form-control" name="www" value="{{ !empty($user['wWWHomePage']) ? $user['wWWHomePage'] : '' }}">
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
      			function add_role() {
      				my_item = '<div></div>';
      				my_item += '<select class="form-control" style="width:50%;display:inline" name="roles[]">';
					@foreach ($roles as $role => $desc)
				    my_item += '<option value="{{ $role }}">{{ $desc }}</option>';
				    @endforeach
					my_item += '</select>';
					my_item += '<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>';
					$('#nrole').before(my_item);
				};

      			function add_character() {
					$('#ncharacter').before('<input type="text" class="form-control" style="width:50%;display:inline" name="character[]" placeholder="請用中文描述，例如：巡迴教師、均一平台管理員...，無則省略。" required>');
					$('#ncharacter').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_tclass() {
      				my_item = '<div></div>';
      				my_item += '<select class="form-control" style="width:25%;display:inline" name="tclass[]">';
					@foreach ($classes as $ou => $desc)
				    my_item += '<option value="{{ $ou }}">{{ $desc }}</option>';
				    @endforeach
					my_item += '</select>';
      				my_item += '<select class="form-control" style="width:25%;display:inline" name="subj[]">';
					@foreach ($subjects as $id => $name)
				    my_item += '<option value="{{ $id }}">{{ $name }}</option>';
				    @endforeach
					my_item += '</select>';
					my_item += '<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().prev().remove();$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>';
					$('#nclass').before(my_item);
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
					$('#nfax').before('<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="fax[]" placeholder="格式如右：(02)23456789">');
					$('#nfax').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_otel() {
					$('#notel').before('<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="otel[]" placeholder="格式如右：(02)23456789">');
					$('#notel').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};

      			function add_htel() {
					$('#nhtel').before('<input type="text" pattern="\(0[0-9]{1,2}\)[0-9]{7,8}" class="form-control" style="width:50%;display:inline" name="htel[]" placeholder="格式如右：(02)23456789">');
					$('#nhtel').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
				};
			</script>
		</div>
		@endif
	</div>
	</div>
</div>
@endsection
