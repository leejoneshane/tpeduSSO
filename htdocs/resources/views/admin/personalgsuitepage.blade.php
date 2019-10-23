@extends('layouts.userboard')

@section('page_heading')
<h1 class="page-header">使用G-Suite服務</h1>
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

		<div style="margin: 10px;">
			@if (empty($date))
			<div style="font-size: 2.1rem;">第一次使用Google Suit for Education服務需要先完成註冊
				<a href="#license" class="btn btn-success" style="margin-left: 4px;" data-toggle="modal" onclick="agreeevent()">
					<span>我要註冊</span>
				</a>
			</div>
			@else
			<div>您已於 {{ date('Y-m-d H:i:s', strtotime($date)) }} 註冊 G-Suite 帳號成功，請選擇要使用的服務</div>
			<div>
				<a href="https://mail.google.com/a/{{$domain}}" target="_blank"><div style="background-image: url('{{ asset('img/gmail-128.png') }}');width: 128px;height: 155px;float: left;margin: 20px;padding-top: 128px;text-align: center;background-repeat: no-repeat;background-size: 100%;cursor: pointer;">Gmail</div></a>
				<a href="https://classroom.google.com/a/{{$domain}}" target="_blank"><div style="background-image: url('{{ asset('img/ic_product_classroom_128.png') }}');width: 128px;height: 155px;float: left;margin: 20px;padding-top: 128px;text-align: center;background-repeat: no-repeat;background-size: 100%;cursor: pointer;">Classroom</div></a>
			</div>
			@endif
		</div>
	</div>

	<div id="license" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<form role="form" method="POST" action="{{ route('personal.gsuiteregister') }}" onsubmit="return checkregisterform()">
						@csrf
						<div class="panel-heading">
							<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
								<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
							</button>
							<h4>註冊G-Suite帳號</h4>
						</div>
						<div class="panel-body">
							<div style="border: 3px solid rgb(0,40,90);padding: 10px 0;border-radius: 4px;">
								<div style="margin: 0 35px;">G-Suite服務註冊說明</div>
								<ul>
									<li>第一次登入使用需要註冊，註冊時間約需5秒鐘，請耐心等候</li>
									<li>導向Google註冊期間請勿按任意鍵，以免產生錯誤</li>
									<li>透過本平臺向G-Suite教育版服務註冊，將提供你的以下資料給Google：帳號、姓名、學校</li>
									<li>你將得到臺北市申請的G-Suite教育版服務帳號為{{ Auth::user()->uname }}@gm.tp.edu.tw</li>
								</ul>
								<div style="margin: 10px 35px 0;">
									<label><input type="checkbox" id="agree"/>&nbsp;&nbsp;已閱讀上述內容，並同意資料傳送</label>
								</div>
								<div id="button-bar" style="text-align: center;margin: 10px 0;">
									<button type="submit" id="register" class="btn btn-primary disabled">確定註冊</button>
								</div>
								<div id="waiting-bar" class="form-group" style="text-align: center;padding-top: 15px;color: brown;display: none;">
									<i class="fa fa-spinner fa-spin" style="font-size:24px"></i>
									<span style="margin-left: 6px;font-size: 21px;">註冊G-Suite帳號中，請稍候...</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		function agreeevent() {
			if(!$("#agree").hasClass("bind")){
				$("#agree").addClass('bind').click(function(){
					if($(this).prop('checked')){
						$("#register").removeClass('disabled');
					}else $("#register").addClass('disabled');
				})
			}
		}

		function checkregisterform() {
			if($("#agree").prop('checked')){
				$("#button-bar").hide();
				$("#waiting-bar").show();
				return true;
			}else{
				return false;
			}
		}
	</script>
</div>
@endsection