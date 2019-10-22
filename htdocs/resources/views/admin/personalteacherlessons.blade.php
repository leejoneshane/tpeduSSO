@extends('layouts.userboard')

@section('page_heading')
<h1 class="page-header">G-Suite Classroom 建立</h1>
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

		<div>教師：{{Auth::user()->name}}&nbsp;&nbsp;
		@if ($gsuite == 'Y')
		請選擇下列你授課的班級科目來建立G-Suite Classroom
		@else
		<span style="color: red">未註冊G-Suite帳號，無法建立Classroom</span>
		@endif
		</div>
		<div class="panel panel-default">
			<div class="panel-heading" style="overflow: hidden;">
				<h4 style="float: left;">教師授課課表</h4>
			</div>
			<div class="panel-body">
				<table class="table table-hover" style="margin: 0;">
					<thead>
						<tr>
							@if ($gsuite == 'Y')
							<th>動作</th>
							@endif
							<th>班級</th>
							<th>科目</th>
							<th>Classroom編號</th>
						</tr>
					</thead>
					<tbody>
					@if (!empty($data))
					@foreach ($data as $d)
						<tr>
							@if ($gsuite == 'Y')
							<td style="vertical-align: inherit;">
								@if(empty($d['code']) && empty($d['link']))
								<button type="button" class="btn btn-danger" onclick="showform('{{ $d['dc'] }}','{{ $d['cls'] }}','{{ $d['subj'] }}','{{ $d['subjName'] }}')">建立</button>
								@endif
							</td>
							@endif
							<td style="vertical-align: inherit;">
								<label>{{ $d['clsName'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['subjName'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								@if(!empty($d['code']) && !empty($d['link']))
								<label><a href="{{ $d['link'] }}" target="_blank">{{ $d['code'] }}</a></label>
								@endif
							</td>
						</tr>
					@endforeach
					@endif
					</tbody>
				</table>
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
								<div class="col-sm-12">教師：{{Auth::user()->name}}&nbsp;&nbsp;請輸入下列資料</div>
							</div>
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