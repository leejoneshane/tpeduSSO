@extends('layouts.login')

@section('content')
<div class="row">
    <div class="col-sm-8 col-sm-offset-2 text shadow">
        <img src="{{ asset('img/TaipeiEduLogo.png') }}" class="img-fluid logo" alt="Responsive image">
        <h1><strong><font style="color:white">臺北市政府教育局</font></strong></h1>
        <div class="description">
    	    <p><strong><font style="color:white;font-size:24pt;">單一身分驗證服務</font></strong></p>
    	</div>
    </div>
</div>
<div class="row">
    <div class="col-sm-4 col-sm-offset-4 form-box">
	<div class="form-top">
            <div class="form-top-left">
                <h3>歡迎使用</h3>
		        @if (session('error') || session('success'))
		            <p style="color:red">{{ session('error') }}{{ session('success') }}</p>
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
                <div class="form-group">
                    <label for="remember" class="col-sm-6 pull-left btn-link">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}> 記住我<br>
                    </label>
                	<!--p>網頁設計：臺北市福星國小　黃永銘</p-->
            	    </label>
            	    <a class="btn-link pull-right" href="{{ route('password.request') }}">忘記帳號、密碼？</a>
                </div>
                <button type="submit" index="0" role="button" class="btn">登入</button>
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
@endsection
