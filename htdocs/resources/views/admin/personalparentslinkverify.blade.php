@extends('layouts.userboard')

@section('page_heading')
<h1 class="page-header">導師審核家長親子連結申請</h1>
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

		<div class="panel panel-default">
			<div class="panel-heading" style="overflow: hidden;">
				<h4 style="float: left;">{{ $clsname }}</h4>
				<div style="float: left;margin-left: 50px;">
					<form name="formQuery" style="margin-top: 5px;">
						<label><input type="radio" name="kind" value="now" {{ $kind == 'now' ? 'checked="checked"':'' }} onclick="document.formQuery.submit()" />&nbsp;待審核案件</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<label><input type="radio" name="kind" value="all" {{ $kind == 'all' ? 'checked="checked"':'' }} onclick="document.formQuery.submit()" />&nbsp;已審核案件</label>
					</form>
				</div>
				@if($kind == 'now')
				<div style="clear: both;"></div>
				<div style="float: left;">
					<span style="font-weight: bold;">批次審核動作</span>
					<label>
						<button type="button" class="btn btn-success" id="agree" style="margin-left: 20px;" onclick="verify2('1')">同意</button>
						<button type="button" class="btn btn-danger" id="disagree" style="margin-left: 30px;" onclick="verify2('2')">不同意</button>
					</label>
				</div>
				@endif
			</div>
			<div class="panel-body">
				<table class="table table-hover" style="margin: 0;">
					<thead>
						<tr>
							<th>項次</th>
							<th>學號</th>
							<th>座號</th>
							<th>學生</th>
							<th>家長</th>
							<th>關係</th>
							<th>提出時間</th>
							<th>審核結果</th>
							<th>審核時間</th>
						</tr>
					</thead>
					<tbody id="tbody">
					@if (!empty($data))
					@foreach ($data as $d)
						<tr>
							<td style="vertical-align: inherit;">
								@if($kind == 'now')
								<label><input type="checkbox" value="{{$d['id']}}"/></label>
								@else
								<label>{{ $d['row'] }}</label>
								@endif
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['employeeNumber'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['seat'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['name'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label><a href="#" onclick="pardata('{{ $d['parent_idno'] }}','{{ $d['parent_relation'] }}','{{ $d['parent_name'] }}','{{ $d['parent_mobile'] }}','{{ $d['parent_email'] }}')">{{ $d['parent_name'] }}</a></label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ $d['parent_relation'] }}</label>
							</td>
							<td style="vertical-align: inherit;">
								<label>{{ date('Y-m-d', strtotime($d['created_at'])) }}<br/>{{ date('H:i:s', strtotime($d['created_at'])) }}</label>
							</td>
							<td style="vertical-align: inherit;">
								@if ($d['status'] == '1')
								<label><span></span>同意</label>
								@elseif($d['status'] == '2')
								<label><span style="color: red;">不同意</span></label>
								@endif
							</td>
							<td style="vertical-align: inherit;">
								@if (!empty($d['verify_tm']))
								<label>{{ date('Y-m-d', strtotime($d['verify_tm'])) }}<br/>{{ date('H:i:s', strtotime($d['verify_tm'])) }}</label>
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

	<div id="pdata" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<div class="panel-body">
						<div style="margin-bottom: 10px;font-size: 20px;font-weight: bold;">家長資訊</div>
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-sm-3 text-md-right control-label">身分證字號：</div>
							<div class="col-sm-7 text-md-left" id="infoIdno"></div>
						</div>
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-sm-3 text-md-right control-label">親子關係：</div>
							<div class="col-sm-7 text-md-left" id="infoRtype"></div>
						</div>
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-sm-3 text-md-right control-label">姓名：</div>
							<div class="col-sm-7 text-md-left" id="infoName"></div>
						</div>
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-sm-3 text-md-right control-label">電話號碼：</div>
							<div class="col-sm-7 text-md-left" id="infoMobile"></div>
						</div>
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-sm-3 text-md-right control-label">eMail信箱：</div>
							<div class="col-sm-7 text-md-left" id="infoEmail"></div>
						</div>
						<div class="form-group" style="text-align: center;padding-top: 15px;">
							<button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-primary" style="margin: 0 14px;">　關閉　</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="apply2" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<form role="form" method="POST" target="_blank">
						@csrf
						<div class="panel-heading" style="border-bottom: 1px solid #888;">
							<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
								<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
							</button>
							<h4 style="font-size: 24px;text-align: center;font-weight: bold;">親子連結審核</h4>
						</div>
						<div id="panelBody" class="panel-body">
							<div id="apply-error" class="alert alert-danger" role="alert" style="display: none;"></div>
							<div class="row" style="margin-bottom: 10px;">
								<div class="col-sm-12 col-xs-12" id="apply2text"></div>
							</div>
							<div id="button-bar" class="form-group" style="text-align: center;padding-top: 15px;">
								<button type="button" class="btn btn-primary" style="margin: 0 14px;" onclick="doverify2()">　確定　</button>
								<button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-primary" style="margin: 0 14px;">　取消　</button>
							</div>
							<div id="waiting-bar" class="form-group" style="text-align: center;padding-top: 15px;color: brown;display: none;">
								<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
								<span style="margin-left: 6px;font-size: 21px;">資料處理中，請稍候...</span>
							</div>
						</div>
						<input type="hidden" id="id"/>
						<input type="hidden" id="yn"/>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		function pardata(idno,rtype,name,mobile,email) {
			$("#infoIdno").text(idno);
			$("#infoRtype").text(rtype);
			$("#infoName").text(name);
			$("#infoMobile").text(mobile);
			$("#infoEmail").text(email);
			$("#pdata").modal('show');
		}

		function verify2(yn) {
			if($("#tbody").find(':checkbox').length){
				var o = $("#tbody").find(':checkbox:checked');
				if(o.length){
					$("#apply2text").html('您將<span style="color: red;">'+(yn=='1'?'':'不')+'同意</span>'+o.length+'筆家長親子連結申請案件，是否確定執行？');
					var ids = '';
					for(var i=0;i<o.length;i++)
						ids += ','+o[i].value;
					$("#id").val(ids.substring(1));
					$("#yn").val(yn);
					$("#apply-error").text('').hide();
					$("#waiting-bar").hide();
					$("#button-bar").show();
					$("#apply2").modal('show');
				}else{
					alert('請先勾選要審核的項目');
				}
			}else{
				alert('沒有申請紀錄');
			}
		}

		function doverify2() {
			$("#apply-error").hide().text('');

			var id = $("#id").val();
			//var apply = $("#panelBody").find("[name='apply']:checked").val();
			var apply = $("#yn").val();
			//var cause = $("#cause").val();

			if(id == ''){
				$("#apply-error").text('缺少系統編號').show();
			}else if(apply != '1' && apply != '2'){
				$("#apply-error").text('請選擇同意或不同意').show();
			}else{
				$("#button-bar").hide();
				$("#waiting-bar").show();

				axios.post('/personal/parentslink_verify',{id:id,apply:apply}).then(res => {
					if(res.data){
						if(res.data.success){
							//$("#applybutton"+res.data.id).parent().html('<label>'+res.data.success+'</label>');
							//$("#apply").modal('hide');
							document.formQuery.submit();
						}else if(res.data.error){
							$("#apply-error").html(res.data.error).show();
							$("#waiting-bar").hide();
							$("#button-bar").show();
						}
					}
				}).catch(function (error) {
					console.log(error);
				});
			}
		}
	</script>
</div>
@endsection