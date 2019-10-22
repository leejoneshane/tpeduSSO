@extends('layouts.login')

@section('content')
<div class="row">
    <div class="col-sm-8 col-sm-offset-2 text shadow">
		<div style="background-image: url('img/TaipeiEduLogo.png');background-repeat: no-repeat;background-position-y: 50%;display: inline-block;padding: 0 70px;">
			<img src="{{ asset('img/TaipeiEduLogo.png') }}" class="img-fluid logo" alt="Responsive image" style="display: none;">
			<h1><strong><font style="color:white">臺北市政府教育局</font></strong></h1>
			<div class="description">
				<p><strong><font style="color:white;font-size:24pt;">單一身分驗證服務</font></strong></p>
			</div>
		</div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4 col-lg-offset-4 col-sm-6 col-sm-offset-3 form-box">
	<div class="form-top">
            <div class="form-top-left">
                <h3>歡迎使用</h3>
		        @if (session('error') || session('success') || $errors->has('captcha'))
		            <p style="color:red">{{ session('error') }}{{ session('success') }}{{$errors->first('captcha')}}</p>
		        @else
                    <p>請輸入您的使用者名稱與密碼：</p>
		        @endif
            </div>
    	    <div class="form-top-right">
                <i class="fa fa-lock"></i>
            </div>
        </div>
	<div class="form-bottom">
            <form role="form" method="POST" action="{{ route('login') }}" class="login-form" data-stage="DataStore1">
                @csrf
                @if(isset($_GET['SAMLRequest']))
        			<input type="hidden" id="SAMLRequest" name="SAMLRequest" value="{{ $_GET['SAMLRequest'] }}">
    			@endif
                @if(isset($_GET['RelayState']))
        			<input type="hidden" id="RelayState" name="RelayState" value="{{ $_GET['RelayState'] }}">
    			@endif				
                <div class="form-group">
                    <label for="username" class="sr-only">登入名稱</label>
                    <input id="username" type="text" placeholder="自訂帳號、電子郵件或手機號碼..." class="form-username form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" autocomplete="off" required autofocus>
                    @if ($errors->has('username'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('username') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="password" class="sr-only">登入密碼</label>
                    <input id="password" type="password" placeholder="密碼..." class="form-password form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" autocomplete="off" required>
                    @if ($errors->has('password'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group code">
                    <label for="captcha" class="sr-only">驗證碼</label>
                    <input type="text" placeholder="驗證碼" class="form-password form-control{{ $errors->has('captcha') ? ' is-invalid' : '' }}" name="captcha" autocomplete="off" required>
                    <img src="{{ captcha_src('flat') }}" onclick="this.src='/captcha/flat?'+Math.random()" title="點圖片重新取得驗證碼">
                 </div>
                <div class="form-group">
                    <label for="remember" class="col-sm-6 pull-left btn-link">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}> 記住我<br>
                    </label>
                	<!--p>網頁設計：臺北市福星國小　黃永銘</p-->
            	    <a class="btn-link pull-right" href="{{ route('password.request') }}">忘記帳號、密碼？</a>
                </div>
                <button type="submit" index="0" role="button" class="btn">登入</button>

				<div class="row">
					<div class="col-xs-12">家長可以使用以下帳號登入</div>
				</div>
				<div class="row">
					<div class="col-xs-4" style="text-align: center;"><a href="/google/link"><img style="padding-left:5px; border=0; cursor: pointer; max-width: inherit;" width=50 height=50 class1="img-responsive center-block" src="{{ asset('img/Google_circle.png') }}" title="透過google帳號登入"></a></div>
					<div class="col-xs-4" style="text-align: center;"><a href="/facebook/link"><img style="padding-left:5px; border=0; cursor: pointer; max-width: inherit;" width=50 height=50 class1="img-responsive center-block" src="{{ asset('img/Facebook_circle.png') }}" title="透過facebook帳號登入"></a></div>
					<div class="col-xs-4" style="text-align: center;"><a href="/yahoo/link"><img style="padding-left:5px; border=0; cursor: pointer; max-width: inherit;" width=50 height=50 class1="img-responsive center-block" src="{{ asset('img/YAHOO_circle.png') }}" title="透過yahoo帳號登入"></a></div>
				</div>
				<hr style="border: 3px inset #BBB;" />
				<div class="row">
					<div class="col-xs-4" style="text-align: center;"><img style="padding-left:5px; border=0; cursor: pointer; max-width: inherit;" width=50 height=50 class1="img-responsive center-block" src="{{ asset('img/help.png') }}" onClick='showHelp()' title="使用說明" /></div>
					<div class="col-xs-4" style="text-align: center;"><a href="/thirdapp"><img style="padding-left:5px; border=0; cursor: pointer; max-width: inherit;" width=50 height=50 class1="img-responsive center-block" src="{{ asset('img/thirdapp.png') }}" title="第三方應用服務專區"></a></div>
					<div class="col-xs-4" style="text-align: center;"><a href="{{ env('QANDA_ADDR') }}" target="_blank"><img style="padding-left:5px; border=0; cursor: pointer; max-width: inherit;" width=50 height=50 class1="img-responsive center-block" src="{{ asset('img/service.png') }}" title="客戶服務網"></a></div>
				</div>				
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3 credits">
	<p>臺北市政府教育局</p>
	<p>地址：臺北市信義區市府路1號8樓</p>
	<p>電話：1999（外縣市請撥02-27208889）#1234</p> <p>信箱：<a href="mailto:edu_ict.19@mail.taipei.gov.tw" target="_top" rel="noreferrer">edu_ict.19@mail.taipei.gov.tw</a></p>
	<!--<p>網頁版型設計：<a href="http://www.fhps.tp.edu.tw" title="臺北市福星國小">臺北市福星國小</a> 黃永銘</p>-->
    </div>
</div>

<script>
function showHelp() {
		
	Swal.fire({
					title: '<h1>操作說明</h1>',
					type: 'info',
					html: 
						'<div id="helpcontent" style="overflow-y: scroll;  height: 600px;  width: 100%;  border: 1px solid #DDD;  padding: 10px; text-align:left;  font-size:16px"> ' +
                        help_content_data +
						'</div>',
					width: 1024,
					showCloseButton: true,
					showCancelButton: false,
					confirmButtonText: '<span class=termsOfAgreement_button>Close</span>'
				}).then(function (result) {
					
				})
}
</script>

@endsection
@section('script')
@parent
<script type="text/javascript">
var help_content_data='';
$(function() {
	$.ajax({
		url:'help_content.html',
		type:'GET',
		success: function(data){
			$('#helpcontent').html($(data).find('#helpcontent').html());
			help_content_data=data;
		}
	});
});
</script>
@endsection