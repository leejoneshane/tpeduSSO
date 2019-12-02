@extends('layouts.userboard')

@section('page_heading')
@endsection

@section('section')
<div class="container">
    <div class="row justify-content-center">
	<div class="col-md-10 col-md-offset-1">
	    <div class="card card-default" style="margin-top: 20px">
		<div class="card-header">親子連結服務</div>
		<div class="card-body">
		@if (session('error'))
		    <div class="alert alert-danger">
			{{ session('error') }}
		    </div>
		@endif
	
		@if (session('success'))
		    <div class="alert alert-success">
			{!! session('success') !!}
		    </div>
		@endif
			<p>請輸入您的孩子個人與就讀學校資料！</p>
		<form class="form-horizontal" name='form1' method="POST" action="{{ route('parents.connectChild') }}">
		    {{ csrf_field() }}
		    <div class="form-group{{ $errors->has('idno') ? ' has-error' : '' }}">
				<label for="idno" class="col-md-3 control-label">身分證字號</label>
				<div class="col-md-7">
					<input id="idno" type="text" class="form-control" name="idno"  placeholder="身分證字號" style="ime-mode:disabled" value="{{ old('idno') ?? $idno }}" required>
					@if ($errors->has('idno'))
					<span class="help-block">
					<strong>{{ $errors->first('idno') }}</strong>
					</span>
					@endif
				</div>
		    </div>
		    <div class="form-group">
			<label for="displayName" class="col-md-3 control-label">就讀學校</label>
				<div class="row">
					<div class="col-md-1 text-md-right control-label">行政區</div>
					<div class="col-md-2 text-md-left">
						<select name="area" class="form-control changeData" style="width: auto" >
							@foreach ($areas as $st)
								<option value="{{ $st }}"{{ (old('area') == $st || $area == $st) ? ' selected' : '' }}>{{ $st }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-1 text-md-right control-label">學層</div>
					<div class="col-md-3 text-md-left">
						<select name="schoolCategory" class="form-control changeData" style="width: auto" >
							@foreach ($schoolCategorys as $sc)
								<option value="{{ $sc }}"{{ (old('schoolCategory') == $sc || $schoolCategory == $sc) ? ' selected' : '' }}>{{ $sc }}</option>
							@endforeach
						</select>
					</div>
				</div>
			 </div>	
			 <div class="form-group">
			 <label for="displayName" class="col-md-3 control-label"></label>
				<div class="row">
					<div class="col-md-1 text-md-right control-label">學校</div>
					<div class="col-md-6 text-md-left">
					<select name="dc" class="form-control" style="width: auto" required>
						@foreach ($schools as $sch)
							<option value="{{ $sch->o }}"{{ (old('dc') == $sch->o || $dc == $sch->o) ? ' selected' : '' }}>{{ $sch->description }}</option>
						@endforeach
					</select>
					</div>
				</div>
		    </div>
            <div class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">
				<label for="student_id" class="col-md-3 control-label">學號</label>
				<div class="col-md-7">
					<input id="student_id" type="text" class="form-control" name="student_id" value="{{ old('student_id', $student_id) }}" style="ime-mode:disabled" required> 
					@if ($errors->has('student_id'))
					<span class="help-block">
					<strong>{{ $errors->first('student_id') }}</strong>
					</span>
					@endif
				</div>
		    </div>
            <div class="form-group{{ $errors->has('student_birthday') ? ' has-error' : '' }}">
				<label for="student_birthday" class="col-md-3 control-label">出生年月日</label>
				<div class="col-md-7">
					<input id="student_birthday" type="text" class="form-control  calendarNOGOOD"  placeholder="yyyymmdd" name="student_birthday" value="{{ old('student_birthday') ?? $student_birthday }}" style="ime-mode:disabled" > 
					@if ($errors->has('student_birthday'))
					<span class="help-block">
					<strong>{{ $errors->first('student_birthday') }}</strong>
					</span>
					@endif
				</div>
		    </div>
		    <div class="form-group">
				<hr style="border: 3px inset #BBB;" />
			</div>		
			<p> 請輸入家長您的資料！</p>
            <div class="form-group">
				<label class="col-md-3 control-label">身分證字號</label>
				<div class="col-md-7">
					<label class="control-label">{{ Auth::user()->idno }}</label>
				</div>
		    </div>
			<div class="form-group{{ $errors->has('relationType') ? ' has-error' : '' }}">
				<label for="pname" class="col-md-3 control-label">親子關係</label>
				<div class="col-md-7" style="margin-top: 6px;">
					<label style="margin: 0;"><input id="relationType" name="relationType" type="radio" value="父親" {{ old('relationType') =='父親' ? 'checked' :'' }} {{ $relationType=='父親' ? 'checked'  :'' }}> 父  </label>&nbsp;&nbsp;&nbsp;
					<label style="margin: 0;"><input id="relationType" name="relationType" type="radio" value="母親" {{ old('relationType') =='母親' ? 'checked' :'' }} {{ $relationType=='母親' ? 'checked'  :'' }}> 母  </label>&nbsp;&nbsp;&nbsp;
					<label style="margin: 0;"><input id="relationType" name="relationType" type="radio" value="監護人" {{ old('relationType') =='監護人' ? 'checked':'' }} {{ $relationType=='監護人' ? 'checked' :'' }}> 監護人(父母以外)  </label>
					@if ($errors->has('relationType'))
					<span class="help-block">
					<strong>{{ $errors->first('relationType') }}</strong>
					</span>
					@endif
				</div>
			</div>
            <div class="form-group{{ $errors->has('pname') ? ' has-error' : '' }}">
				<label for="pname" class="col-md-3 control-label">姓名</label>
				<div class="col-md-7">
					<input id="pname" type="text" class="form-control" name="pname" value="{{ old('pname') ?? $pname }}" style="ime-mode:disabled" > 
					@if ($errors->has('pname'))
					<span class="help-block">
					<strong>{{ $errors->first('pname') }}</strong>
					</span>
					@endif
				</div>
		    </div>
		    <div class="form-group">
			<div class="col-md-10 col-md-offset-4">
			    <button type="submit" class="btn btn-primary" id='buttonSubmit' name='buttonSubmit' value="send">
				確認送出
			    </button>
			</div>
		    </div>
		</form>
	        </div>
	    </div>
	</div>
    </div>

	<div id="pcApply" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<div class="panel-heading">
						<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
							<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
						</button>
						<h4>親子連結申請服務</h4>
					</div>
					<div class="panel-body">
						<div id="applyError" class="alert alert-danger" style="display: none;"></div>
						<div>您輸入您孩子個人與就讀學校資料正確，但系統無此家長資料，請再確認家長資料是否正確？如果資料正確，請確認與更正您的聯絡資訊後，點選[提出親子連結申請]，如果導師審核同意後，系統會寄發email通知您，如果很久沒有收到通知，也沒有建立親子連結，請逕行聯絡您孩子的導師。</div>
						<div style="border: 2px solid rgb(0,53,106);padding: 4px 12px;">
							<div style="margin-bottom: 5px;font-size: 20px;font-weight: bold;color: rgb(0,53,106);">請再確認家長您的資料</div>
							<div class="row" style="margin-bottom: 5px;">
								<div class="col-md-3 text-md-right control-label">身分證字號</div>
								<div class="col-md-7 text-md-left">{{ Auth::user()->idno }}</div>
							</div>
							<div class="row" style="margin-bottom: 5px;">
								<div class="col-md-3 text-md-right control-label">親子關係</div>
								<div class="col-md-7 text-md-left" id="rtypeDiv"></div>
							</div>
							<div class="row" style="margin-bottom: 5px;">
								<div class="col-md-3 text-md-right control-label">姓名</div>
								<div class="col-md-7 text-md-left" id="pnameDiv"></div>
							</div>
							<div style="margin-bottom: 5px;font-size: 20px;font-weight: bold;color: rgb(0,53,106);">請確認與更正聯絡資訊</div>
							<div class="row" style="margin-bottom: 5px;">
								<div class="col-md-3 text-md-right control-label">eMail信箱</div>
								<div class="col-md-7 text-md-left">
									<input type="text" id="email" class="form-control" value="{{ $email }}" /> 
									<span style="display: none;" class="help-block"><strong></strong></span>
								</div>
							</div>
							<div class="row" style="margin-bottom: 5px;">
								<div class="col-md-3 text-md-right control-label">電話號碼</div>
								<div class="col-md-7 text-md-left">
									<input type="text" id="mobile" class="form-control" value="{{ $mobile }}" /> 
									<span style="display: none;" class="help-block"><strong></strong></span>
								</div>
							</div>
						</div>
						<div id="button-bar" style="text-align: center;">
							<button type="button" id="apply" style="margin: 15px;" class="btn btn-default" onclick="sendapply()">提出親子連結申請</button>
							<button type="button" data-dismiss="modal" aria-label="Close" style="margin: 15px;" class="btn btn-default">取消</button>
						</div>
						<div id="waiting-bar" class="form-group" style="text-align: center;padding-top: 15px;color: brown;display: none;">
							<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
							<span style="margin-left: 6px;font-size: 21px;">送出申請中，請稍候...</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="result" class="modal fade">
		<div class="modal-dialog" style="display: -ms-flexbox;display: flex;-ms-flex-align: center;align-items: center;min-height: calc(100% - (.5rem * 12));">
			<div class="modal-content" style="width: 100%;">
				<div class="panel-default">
					<div class="panel-heading">
						<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
							<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
						</button>
						<h4>親子連結申請服務</h4>
					</div>
					<div class="panel-body"></div>
					<div style="text-align: center;">
						<button type="button" data-dismiss="modal" aria-label="Close" style="margin: 15px;" class="btn btn-default">關閉</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('script')
@parent
<script type="text/javascript">
	function sendapply() {
		$("#applyError").hide().text('');

		var email = $.trim($("#email").val());
		var mobile = $.trim($("#mobile").val());
		var post = {
			idno:'{{ session('idno') }}',
			dc:'{{ session('dc') }}',
			stdno:'{{ session('stdno') }}',
			birth:'{{ session('birth') }}',
			rtype:'{{ session('rtype') }}',
			pname:'{{ session('pname') }}'
		};

		if(email == ''){
			$("#email").parent().addClass('has-error');
			$("#email").next().show().find('strong').text('eMail信箱為必填！');
		}else if(email.split('@').length != 2 || email.length < 8 || email.slice(-1) == '.' || email.split('@')[1].split('.').length < 2){
			$("#email").parent().addClass('has-error');
			$("#email").next().show().find('strong').text('eMail信箱格式不正確！');
		}else{
			$("#email").parent().removeClass('has-error');
			$("#email").next().hide().find('strong').text('');
			post.email = email;
		}

		if(mobile == ''){
			$("#mobile").parent().addClass('has-error');
			$("#mobile").next().show().find('strong').text('電話號碼為必填！');
		}else{
			$("#mobile").parent().removeClass('has-error');
			$("#mobile").next().hide().find('strong').text('');
			post.mobile = mobile;
		}

		if(Object.keys(post).length == 8){
			$("#button-bar").hide();
			$("#waiting-bar").show();

			axios.post('/parents/connectApply',post).then(res => {
				if(res.data){
					if(res.data.success){
						window.location.href = '{{route('parents.listConnectChildren')}}';
						//$("#pcApply").modal('hide');
						//$("#result").find(".panel-body").html(res.data.success);
						//$("#result").modal('show');
					}else if(res.data.error){
						$("#applyError").text(res.data.error).show();
					}else{
						if(res.data.email){
							$("#email").parent().addClass('has-error');
							$("#email").next().show().find('strong').text(res.data.email);
						}

						if(res.data.mobile){
							$("#mobile").parent().addClass('has-error');
							$("#mobile").next().show().find('strong').text(res.data.mobile);
						}
					}
				}

				$("#waiting-bar").hide();
				$("#button-bar").show();
			}).catch(function (error) {
				console.log(error);
			});
		}
	}

$(document).ready(function(){
	$('.changeData').change(function(){
		$('form[name=form1]').attr('action','{{ url()->current() }}');
		$('form[name=form1]').submit();
	});
});
</script>
@if (session('askApply'))
<script type="text/javascript">
	$("#rtypeDiv").text('{{ session('rtype') }}');
	$("#pnameDiv").text('{{ session('pname') }}');
	$("#email").focus();
	window.onload = function() { $("#pcApply").modal('show'); };
</script>
@endif
@endsection