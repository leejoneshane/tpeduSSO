@extends('layouts.userboard')

@section('page_heading')
<h1 class="page-header">導師班學生管理</h1>
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
					<a href="#printqrcode" class="btn btn-success" data-toggle="modal" style="float: right;margin-top: 3px;" onclick="parents_list()">
						<span style="margin-left: 4px;">列印家長QR-Code</span>
					</a>
					<h4 style="float: left;">{{ $clsname }}</h4>
				</div>
				<div class="panel-body">
					<table class="table table-hover" style="margin: 0;">
						<thead>
							<tr>
								<th>狀態</th>
								<th>帳號</th>
								<th>身分證字號</th>
								<th>姓名</th>
								<th>班級</th>
								<th>座號</th>
								<th>管理</th>
							</tr>
						</thead>
						<tbody>
						@if (!empty($data))
						@foreach ($data as $d)
							<tr>
								<td style="vertical-align: inherit;">
									<label>{{ $d['status'] }}</label>
								</td>
								<td style="vertical-align: inherit;">
									<label>{{ $d['uid'] }}</label>
								</td>
								<td style="vertical-align: inherit;">
									<label>{{ $d['cn'] }}</label>
								</td>
								<td style="vertical-align: inherit;">
									<label>{{ $d['displayName'] }}</label>
								</td>
								<td style="vertical-align: inherit;">
									<label>{{ $d['tpClass'] }}</label>
								</td>
								<td style="vertical-align: inherit;">
									<label>{{ $d['tpSeat'] }}</label>
								</td>
								<td>
									<button type="button" class="btn btn-primary"
										onclick="if(confirm('確定要回復{{$d['displayName']}}的密碼?')){$('#resetpwd').attr('action','{{ route('personal.resetpwStudent', [ 'uuid' => $d['entryUUID'] ]) }}').submit();}">回復密碼</button>
									<button type="button" class="btn btn-danger" onclick="showmyparents('{{$d['entryUUID']}}')">家長連結管理</button>
								</td>
							</tr>
						@endforeach
						@endif
						</tbody>
					</table>
				</div>
			</div>
    
		<form id="resetpwd" action="" method="POST" style="display: none;">
		@csrf
		</form>
	</div>

	<div id="printqrcode" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<form role="form" method="POST" action="{{ route('personal.listparentsqrcode') }}" target="_blank" onsubmit="return checkprintform()">
						@csrf
						<div class="panel-heading">
							<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
								<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
							</button>
							<h4>列印親子連結家長QR-CODE</h4>
							@if (!empty($expire))
							<div style="text-align: right;">QR-CODE有效期限：{{ $expire }}</div>
							@endif
						</div>
						<div class="panel-body">
							<table style="width: 100%;" border="1">
								<thead>
									<tr>
										<td style="text-align: center;width: 66px;"><input type="checkbox" id="checkall" onclick="chkall(this)"/><label>列印否</label></td>
										<td style="text-align: center;"><label>座號</label></td>
										<td style="text-align: center;"><label>學生姓名</label></td>
										<td style="text-align: center;"><label>家長姓名</label></td>
										<td style="text-align: center;"><label>關係</label></td>
										<td style="text-align: center;"><label>備註</label></td>
									</tr>
								</thead>
								<tbody id="tbody"></tbody>
							</table>
							<div id="printDiv" class="form-group" style="text-align: center;padding-top: 15px;"></div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div id="myparents" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<div class="panel-heading">
						<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
							<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
						</button>
						<h4>家長連結管理</h4>
					</div>
					<div class="panel-body">
						<table id="mtable" style="width: 100%;" border="1">
							<thead>
								<tr>
									<td style="text-align: center;"><label>狀態</label></td>
									<td style="text-align: center;"><label>身分證字號</label></td>
									<td style="text-align: center;"><label>家長姓名</label></td>
									<td style="text-align: center;"><label>關係</label></td>
									<td style="text-align: center;"><label>產生QR-CODE</label></td>
									<td style="text-align: center;"><label>連結</label></td>
								</tr>
							</thead>
							<tbody id="mbody"></tbody>
						</table>
						<div id="qrtable" style="width: 100%;font-family: 微軟正黑體;font-size: 27px;display: none;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		function chkall(o) {
			$('#tbody').find(":checkbox").prop('checked',$(o).prop('checked'));
		}

		function checkprintform() {
			$("#printDiv").find(".errorPringMsg").remove();
			if($('#tbody').find('input:checked').length)
				return true;

			$('#printDiv').append('<div class="errorPringMsg" style="color: red;">請先勾選要列印的項目</div>');
			return false;
		}

		function parents_list() {
			$('#tbody').html('');
			axios.get('/personal/listparents').then(res => {
				res.data.forEach(
					function add_options(o) {
						$('#tbody').append('<tr><td style="text-align: center;"><input type="checkbox" name="pid[]" value="'+o.id+'" /></td><td style="text-align: center;">'+o.seat+'</td><td>'+o.sname+'</td><td>'+o.name+'</td><td style="text-align: center;">'+o.rel+'</td><td></td></tr>');
					}
				);

				if(!res.data || !res.data.length){
					$('#tbody').append('<tr><td colspan="6" style="text-align: center;">沒有未建立親子關係，且已有身分證字號的家長</td></tr>');
					$('#printDiv').html('');
				}else{
					$('#printDiv').html('<button type="submit" class="btn btn-success">列印</button>');
				}
			}).catch(function (error) {
				console.log(error);
			});
		}

		function showmyparents(id) {
			$('#mbody').html('');
			axios.get('/personal/listmyparents/'+id).then(res => {
				res.data.forEach(
					function add_options(o) {
						var html = '<tr><td style="text-align: center;">'+$.trim(o.status)+'</td><td style="text-align: center;">'+$.trim(o.idno)+'</td><td>'+$.trim(o.name)+'</td><td style="text-align: center;">'+$.trim(o.rel)+'</td><td style="text-align: center;">';
						if(o.from == 'D'){
							if(o.idno){
								html += "<button type=\"button\" class=\"btn btn-success\" style=\"padding: 3px 20px;margin: 3px;\" onclick=\"showqrcode('"+o.sid+"','"+o.id+"')\">產生</button>";
							}else html += '<span style="color: #CCC;">缺少身分證無法產生</span>';
						}else html += '<span style="color: #CCC;">已建立親子關係</span>';
						html += '</td><td style="text-align: center;" data="'+((o.from=='R'&&o.linked=='1')?'Y':'N')+'">';
						if(o.from == 'R'){
							html += "<label><input type=\"radio\" name=\"_link"+o.id+"\" "+(o.linked == '1'?'checked="checked"':'')+"value=\"Y\" onclick=\"linkedchg('"+o.sid+"','"+o.id+"',this)\"/>"
								+"<i class=\"glyphicon glyphicon-ok\" style=\"color: #5cb85c;cursor: pointer;\"></i></label>"
								+"&nbsp;&nbsp;<label><input type=\"radio\" name=\"_link"+o.id+"\" "+(o.linked != '1'?'checked="checked"':'')+"value=\"N\" onclick=\"linkedchg('"+o.sid+"','"+o.id+"',this)\"/>"
								+"<i class=\"glyphicon glyphicon-remove\" style=\"color: #d9534f;cursor: pointer;\"></i></label>";
						}
						html += '</td></tr>';
						$('#mbody').append(html);
					}
				);
				$("#mtable").show();
				$("#qrtable").hide();
				$('#myparents').modal('show');
			}).catch(function (error) {
				console.log(error);
			});
		}

		function showqrcode(uuid,id) {
			axios.post('/personal/parentsqrcode',{uuid:uuid,id:id}).then(res => {
				if(res.data){
					if(res.data.base64){
						var o = res.data;
						$("#qrtable").html('<div>'+o.cls+'班&nbsp;&nbsp;'+o.seat+'號&nbsp;&nbsp;'+o.sname+'</div>')
							.append('<div>'+o.name+'&nbsp;&nbsp;'+o.rel+'</div>')
							.append('<div style="text-align: center;margin: 20px 0;"><img src="data:image/png;base64, '+o.base64+'" style="width: 220px;" /></div>')
							.append('<div style="text-align: center;"><button type="button" class="btn btn-success" class="">返回</button></div>').find("button").click(function(){
								$("#qrtable").hide();
								$("#mtable").show();
							});
						$("#mtable").hide();
						$("#qrtable").show();
					}else if(res.data.error){
						alert(res.data.error);
					}else{
						alert('產生QR-CODE時發生錯誤');
					}
				}
			}).catch(function (error) {
				console.log(error);
			});
		}

		function linkedchg(uuid,id,o) {
			var old = $(o).parent().parent().attr("data");
			var val = o.value;

			if(old != val){
				axios.post('/personal/linkedChange',{uuid:uuid,id:id,c:val}).then(res => {
					if(res.data){
						if(res.data.success){
							//if(c) $(o).removeClass('glyphicon-ok').addClass('glyphicon-remove').css("color","#d9534f");
							//else $(o).removeClass('glyphicon-remove').addClass('glyphicon-ok').css("color","#5cb85c");
							alert('已'+(val=='Y'?'連結':'中斷')+'親子關係');
							$(o).parent().parent().attr("data",val);
						}else if(res.data.error){
							val = (val=='Y'?'N':'Y');
							$(o).parent().parent().find("[value='"+val+"']").prop('checked',true);
							alert(res.data.error);
						}else{
							val = (val=='Y'?'N':'Y');
							$(o).parent().parent().find("[value='"+val+"']").prop('checked',true);
							alert('設定親子關係時發生錯誤');
						}
					}
				}).catch(function (error) {
					console.log(error);
				});
			}

			return false;
		}
	</script>
</div>
@endsection