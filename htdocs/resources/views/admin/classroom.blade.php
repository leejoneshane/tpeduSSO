@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
	<div class="col-md-8 col-md-offset-2">
	    <div class="card card-default" style="margin-top: 20px">
		<div class="card-header">建立 Google 課堂</div>
		<div class="card-body">
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
		@if (empty($data))
		<p>因為學校未配課給您，所以無法為您建立 Google 課堂！</p>
		@else
		<p>系統會自動將您挑選的班級！</p>
		<form class="form-horizontal" method="POST" action="{{ route('classroom') }}">
		@csrf
			<div class="form-group">
				<label for="teachClass">請選擇任教班級和科目：</label>
				<select class="form-control" name="teachClass">
				@foreach ($data as $pair => $title)
					<option value="{{ $pair }}">{{ $title }}</option>
				@endforeach
				</select>
			</div>
			<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
				<label for="name" class="col-md-4 col-form-label text-md-right">課程名稱</label>
				<div class="col-md-8">
				    <input type="text" class="form-control" name="name" required>
				    @if ($errors->has('name'))
			    	<span class="help-block">
						<strong>{{ $errors->first('name') }}</strong>
			    	</span>
			    	@endif
				</div>
		    </div>
			<div class="form-group{{ $errors->has('brief') ? ' has-error' : '' }}">
				<label for="brief" class="col-md-4 col-form-label text-md-right">課程簡述</label>
				<div class="col-md-8">
				    <textarea name="brief" maxlength="200" class="form-control" required></textarea>
				    @if ($errors->has('brief'))
			    	<span class="help-block">
						<strong>{{ $errors->first('brief') }}</strong>
			    	</span>
			    	@endif
				</div>
		    </div>
			<div class="form-group{{ $errors->has('brief') ? ' has-error' : '' }}">
				<label for="brief" class="col-md-4 col-form-label text-md-right">課程簡述</label>
				<div class="col-md-8">
				    <textarea name="brief" maxlength="200" class="form-control" required></textarea>
				    @if ($errors->has('brief'))
			    	<span class="help-block">
						<strong>{{ $errors->first('brief') }}</strong>
			    	</span>
			    	@endif
				</div>
		    </div>
		    <div class="form-group">
				<div class="col-md-8 col-md-offset-4">
			    	<button type="submit" class="btn btn-primary">建立課程</button>
				</div>
		    </div>
		</form>
	    </div>
	    </div>
	</div>
    </div>
</div>

	<div id="apply" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<form role="form" method="POST" action="{{ route('personal.teacher_courses') }}" target="_blank">
						@csrf
						<div class="panel-heading" style="border-bottom: 1px solid #888;">
							<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
								<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
							</button>
							<h4 style="font-size: 24px;text-align: center;font-weight: bold;">G-Suite Classroom 建立</h4>
						</div>
						<div id="panelBody" class="panel-body">
							<div id="create-error" class="alert alert-danger" role="alert" style="display: none;"></div>
							<div class="row" style="margin-bottom: 10px;">
								<div class="col-sm-2 col-xs-12">課程名稱</div>
								<div class="col-sm-10 col-xs-12">
									<input type="text" name="subjName" maxlength="50" style="width: 100%;"/>
									<p style="color: #a94442;display: none;" class="error-block"><strong></strong></p>
								</div>
							</div>
							<div class="row" style="margin-bottom: 10px;">
								<div class="col-sm-2 col-xs-12">課程簡述</div>
								<div class="col-sm-10 col-xs-12">
									<textarea name="brief" maxlength="200" style="width: 100%;height: 60px;"></textarea>
									<p style="color: #a94442;display: none;" class="error-block"><strong></strong></p>
								</div>
							</div>
							<div class="row" style="margin-bottom: 10px;">
								<div class="col-sm-2 col-xs-12">授課教師</div>
								<div class="col-sm-10 col-xs-12"><table id="teachers" border="1" width="100%"></table></div>
							</div>
							<div class="row" style="margin-bottom: 10px;">
								<div class="col-sm-2 col-xs-12">
									<div>上課學生</div>
									<div style="text-align: center;"><input type="checkbox" id="checkall" onclick="chkall(this)"/>全選</div>
								</div>
								<div class="col-sm-10 col-xs-12">
									<div style="overflow: auto;max-height: 270px;">
										<table id="students" border="1" width="100%"></table>
									</div>
								</div>
								<p style="color: #a94442;display: none;" class="error-block"><strong></strong></p>
							</div>
							<div id="button-bar" class="form-group" style="text-align: center;padding-top: 15px;">
								<button type="button" id="create" class="btn btn-primary" style="margin: 0 14px;" onclick="createCourse()">建立課程</button>
								<button type="button" id="cancel" data-dismiss="modal" aria-label="Close" class="btn btn-primary" style="margin: 0 14px;">　取消　</button>
							</div>
							<div id="waiting-bar" class="form-group" style="text-align: center;padding-top: 15px;color: brown;display: none;">
								<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
								<span style="margin-left: 6px;font-size: 21px;">課程建立中，請稍候...</span>
							</div>
						</div>
						<input type="hidden" id="subjkey" name="subjkey"/>
					</form>
					<form id="form2" role="form" action="/personal/teacher_lessons">@csrf</form>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		function chkall(o) {
			$('#students').find(":checkbox").prop('checked',$(o).prop('checked'));
		}

		function createCourse() {
			$("#create-error").hide().text('');
			$("#checkall").prop('checked',false);

			var o = $("#panelBody");
			o.find(".error-block").hide().find("strong").text('');

			var subjkey = $("#subjkey").val();
			var name = $.trim(o.find("[name='subjName']").val());
			var brief = $.trim(o.find("[name='brief']").val());
			var pid = [];

			$.each($('#students').find(":checkbox:checked"), function(i,x){
				pid.push(x.value);
			})

			if(name == ''){
				o.find("[name='subjName']").next().show().find("strong").text('課程名稱是必填欄位');
			//}else if(brief == ''){
			//	o.find("[name='brief']").next().show().find("strong").text('課程簡述是必填欄位');
			//}else if($('#students').find(":checkbox:checked").length < 1){
			//	$("#students").parent().parent().next().show().find("strong").text('');
			}else{
				$("#button-bar").hide();
				$("#waiting-bar").show();

				axios.post('/personal/teacher_courses',{subjkey:subjkey,subjName:name,brief:brief,pid:pid}).then(res => {
					if(res.data){
						if(res.data.error){
							$("#create-error").text(res.data.error).show();
							$("#apply")[0].scrollTop = 0;
						}else if(res.data.success){
							$("#form2").submit();
						}
					}

					$("#waiting-bar").hide();
					$("#button-bar").show();
				}).catch(function (error) {
					console.log(error);
					$("#waiting-bar").hide();
					$("#button-bar").show();
				});
			}
		}

		function showform(dc,cls,subj,name) {
			axios.post('/personal/lessons_member',{dc:dc,cls:cls,subj:subj}).then(res => {
				$("#create-error").hide().text('');
				$("#apply").find("[name='subjName']").val(name).end().find("[name='brief']").val('');
				$("#teachers").html('');
				$("#students").html('');

				if(res.data){
					if(res.data.teachers && res.data.teachers.length){
						res.data.teachers.forEach(
							function add_options(o) {
								//var html = '<tr><td style="text-align: center;width: 45px;"><input type="checkbox" name="pid[]" value="'+o.entryUUID+'" /></td><td style="text-align: left;width: 23%;min-width: 60px;word-break: break-all;padding: 1px 2px;">'+o.displayName+'</td><td style="text-align: left;word-break: break-all;padding: 1px 2px;">'+o.mail+'</td></tr>'
								var html = '<tr><td style="text-align: left;width: 23%;min-width: 60px;word-break: break-all;padding: 1px 2px;">'+o.displayName+'</td><td style="text-align: left;word-break: break-all;padding: 1px 2px;">';
								if(o.mail) html += o.mail;
								else html += '<span style="font-style: italic;text-align: center;color: #CCC;">未註冊G-Suite帳號</span>';
								html += '</td></tr>';
								$('#teachers').append(html);
							}
						);
					}else{
						$('#teachers').append('<tr><td style="text-align: center;font-weight: bold;">沒有授課教師</td></tr>');
					}

					if(res.data.students && res.data.students.length){
						
						res.data.students.forEach(
							function add_options(o) {
								var html = '<tr><td style="text-align: center;width: 45px;">';
								if(o.mail) html += '<input type="checkbox" name="pid[]" value="'+o.entryUUID+'" />';
								html += '</td><td style="text-align: left;width: 23%;min-width: 60px;word-break: break-all;padding: 1px 2px;">'+o.displayName+'</td><td style="text-align: left;word-break: break-all;padding: 1px 2px;">';
								if(o.mail) html += o.mail;
								else html += '<span style="font-style: italic;text-align: center;color: #CCC;">未註冊G-Suite帳號</span>';
								html += '</td></tr>'
								$('#students').append(html);
							}
						);

						$("#students").parent()[0].scrollTop = 0;
					}else{
						$('#students').append('<tr><td style="text-align: center;font-weight: bold;">沒有上課學生</td></tr>');
					}

					$("#subjkey").val(dc+','+cls+','+subj);
					$('#apply').modal('show');
				}
			}).catch(function (error) {
				console.log(error);
			});
		}
	</script>
</div>
@endsection