@extends('layouts.superboard')

@section('page_heading')
新增人員資訊
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
			<h4>新增人員資訊</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('bureau.createPeople') }}">
		    	@csrf
			    <div class="form-group{{ $errors->has('idno') ? ' has-error' : '' }}">
					<label>身分證字號</label>
					<input id="idno" type="text" class="form-control" name="idno" value="" required>
					@if ($errors->has('idno'))
						<p class="help-block">
							<strong>{{ $errors->first('idno') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('sn') ? ' has-error' : '' }}">
					<label>姓氏</label>
					<input id="sn" type="text" class="form-control" name="sn" value="" required>
					@if ($errors->has('sn'))
						<p class="help-block">
							<strong>{{ $errors->first('sn') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('gn') ? ' has-error' : '' }}">
					<label>名字</label>
					<input id="gn" type="text" class="form-control" name="gn" value="" required>
					@if ($errors->has('gn'))
						<p class="help-block">
							<strong>{{ $errors->first('gn') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
				<label style="display:block">隸屬機構</label>
					@if (isset($user) && is_array($user['o']))
						@foreach ($user['o'] as $o)
							<select class="form-control" style="width:25%;display:inline" name="area[]" onchange="refresh_orgs(this)">
							@foreach ($areas as $st)
				    		<option value="{{ $st }}"{{ $st == $area ? ' selected' : '' }}>{{ $st }}</option>
					    	@endforeach
							</select>
							<select class="form-control" style="width:35%;display:inline" name="o[]">
							@foreach ($schools as $o => $desc)
			    			<option value="{{ $o }}"{{ $dc == $o ? ' selected' : '' }}>{{ $desc }}</option>
			    			@endforeach
							</select>
							@if (!$loop->first)
							<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().prev().remove();$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>
							@endif
						@endforeach
					@else
						<select class="form-control" style="width:25%;display:inline" name="area[]" onchange="refresh_orgs(this)">
							@foreach ($areas as $st)
				    		<option value="{{ $st }}"{{ $st == $area ? ' selected' : '' }}>{{ $st }}</option>
					    	@endforeach
						</select>
						<select class="form-control" style="width:35%;display:inline" name="o[]">
							@foreach ($schools as $o => $desc)
			    			<option value="{{ $o }}"{{ $dc == $o ? ' selected' : '' }}>{{ $desc }}</option>
			    			@endforeach
						</select>
					@endif
					<button id="no" type="button" class="btn btn-primary btn-circle" onclick="add_org()"><i class="fa fa-plus"></i></button>
				</div>
			    <div class="form-group">
					<label>身份別</label>
					<select id="type" class="form-control" name="type" onchange="switchtype()">
					@foreach ($types as $type)
			    		<option value="{{ $type }}">{{ $type }}</option>
			    	@endforeach
					</select>
				</div>
				<div id="student" style="display: none;">
			    	<div class="form-group{{ $errors->has('stdno') ? ' has-error' : '' }}">
						<label>學號</label>
						<input id="stdno" type="text" class="form-control" name="stdno">
						@if ($errors->has('stdno'))
							<p class="help-block">
								<strong>{{ $errors->first('stdno') }}</strong>
							</p>
						@endif
					</div>
				    <div class="form-group{{ $errors->has('tclass') ? ' has-error' : '' }}">
						<label>就讀班級</label>
						<select id="tclass" class="form-control" name="tclass">
						@foreach ($classes as $ou => $desc)
			    			<option value="{{ $ou }}">{{ $desc }}</option>
				    	@endforeach
						</select>
					</div>
				    <div class="form-group{{ $errors->has('seat') ? ' has-error' : '' }}">
						<label>座號</label>
						<input id="seat" type="text" class="form-control" name="seat">
						@if ($errors->has('seat'))
							<p class="help-block">
								<strong>{{ $errors->first('seat') }}</strong>
							</p>
						@endif
					</div>
				</div>
			    <div class="form-group{{ $errors->has('character') ? ' has-error' : '' }}">
					<label style="display:block">特殊身份註記</label>
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
						<option value="0"{{ isset($user) && $user['gender'] == 0 ? ' selected' : '' }}>未知</option>
						<option value="1"{{ isset($user) && $user['gender'] == 1 ? ' selected' : '' }}>男</option>
						<option value="2"{{ isset($user) && $user['gender'] == 2 ? ' selected' : '' }}>女</option>
						<option value="9"{{ isset($user) && $user['gender'] == 9 ? ' selected' : '' }}>其它</option>
					</select>
				</div>
			    <div class="form-group{{ $errors->has('birth') ? ' has-error' : '' }}">
					<label>出生日期</label>
					<input id="birth" type="date" class="form-control" name="birth">
					@if ($errors->has('birth'))
						<p class="help-block">
							<strong>{{ $errors->first('birth') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mail') ? ' has-error' : '' }}">
					<label style="display:block">電子郵件（只有主要電子郵件可用於登入和重設密碼）</label>
						<button id="nmail" type="button" class="btn btn-primary btn-circle" onclick="add_mail()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('mail'))
						<p class="help-block">
							<strong>{{ $errors->first('mail') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
					<label style="display:block">手機號碼（只有主要手機號碼可用於登入和重設密碼）</label>
						<button id="nmobile" type="button" class="btn btn-primary btn-circle" onclick="add_mobile()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('mobile'))
						<p class="help-block">
							<strong>{{ $errors->first('mobile') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('fax') ? ' has-error' : '' }}">
					<label style="display:block">傳真號碼</label>
						<button id="nfax" type="button" class="btn btn-primary btn-circle" onclick="add_fax()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('fax'))
						<p class="help-block">
							<strong>{{ $errors->first('fax') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('otel') ? ' has-error' : '' }}">
					<label style="display:block">辦公電話</label>
						<button id="notel" type="button" class="btn btn-primary btn-circle" onclick="add_otel()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('otel'))
						<p class="help-block">
							<strong>{{ $errors->first('otel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('htel') ? ' has-error' : '' }}">
					<label style="display:block">住家電話</label>
						<button id="nhtel" type="button" class="btn btn-primary btn-circle" onclick="add_htel()"><i class="fa fa-plus"></i></button>
					@if ($errors->has('htel'))
						<p class="help-block">
							<strong>{{ $errors->first('htel') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('raddress') ? ' has-error' : '' }}">
					<label style="display:block">戶籍地址</label>
					<input id="raddress" type="text" class="form-control" name="raddress" placeholder="請包含里鄰資訊...">
					@if ($errors->has('raddress'))
						<p class="help-block">
							<strong>{{ $errors->first('raddress') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
					<label style="display:block">郵寄地址</label>
					<input id="address" type="text" class="form-control" name="address">
					@if ($errors->has('address'))
						<p class="help-block">
							<strong>{{ $errors->first('address') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('www') ? ' has-error' : '' }}">
					<label>個人首頁</label>
					<input id="www" type="text" class="form-control" name="www">
					@if ($errors->has('www'))
						<p class="help-block">
							<strong>{{ $errors->first('www') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<button type="submit" class="btn btn-success">新增</button>
				</div>
			</form>
			<script type="text/javascript">
				function refresh_orgs(obj) {
					st = $(obj).val();
					axios.get('/bureau/orgs/' + st)
    					.then(response => {
    						$(obj).next().find('option').remove();
        					response.data.forEach(
        						function add_option(org) { $(obj).next().append('<option value="' + org.o + '">' + org.description + '</option>'); }
        					);
							o = $(obj).next().find('option:first').val();
		  					refresh_classes(o);
    					})
						.catch(function (error) {
							console.log(error);
  						});
      			};
      			
				function refresh_classes(o) {
					$('#tclass').find('option').remove();
					axios.get('/bureau/classes/' + o)
    					.then(response => {
        					response.data.forEach(
        						function add_option(tclass) { $('#tclass').append('<option value="' + tclass.ou + '">' + tclass.description + '</option>'); }
        					);
    					})
						.catch(function (error) {
							console.log(error);
  						});
  					$('#ou option:first').attr('selected', true);
      			};
      			
				function add_org() {
					my_item = '<div></div>';
      				my_item += '<select class="form-control" style="width:25%;display:inline" name="area[]" onchange="refresh_orgs(this)">';
					my_item += '<option value="">請選擇</option>';
					@foreach ($areas as $st)
				    my_item += '<option value="{{ $st }}">{{ $st }}</option>';
				    @endforeach
					my_item += '</select>';
      				my_item += '<select class="form-control" style="width:35%;display:inline" name="o[]">';
					my_item += '</select>';
					my_item += '<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().prev().remove();$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>';
					$('#no').before(my_item);
				};

      			function switchtype() {
      				if ($('#type').val() == '學生') {
      					$('#student').show();
      				} else {
      					$('#student').hide();
      				}	
				}
				
       			function add_character() {
					$('#ncharacter').before('<input type="text" class="form-control" style="width:50%;display:inline" name="character[]" placeholder="請用中文描述，例如：巡迴教師、均一平台管理員...，無則省略。" required>');
					$('#ncharacter').before('<button type="button" class="btn btn-danger btn-circle" onclick="$(this).prev().remove();$(this).remove();"><i class="fa fa-minus"></i></button>');
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
