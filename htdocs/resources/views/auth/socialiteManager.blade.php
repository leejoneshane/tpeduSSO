@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header"><h4>社群帳號綁定</h4></div>
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
                <p>社群帳號綁定說明：
                    <ul>
                        <li>警告：社群帳號經過綁定後，第三方應用即可從登入軌跡得知您的真實身分，該社群帳號將喪失匿名特性。</li>
                        <li>綁定社群帳號前，應先刪除瀏覽器快取資料，或使用「無痕視窗」、「私密瀏覽」...等模式，以避免綁定其他人的帳號。</li>
                        <li>不應該綁定父母或朋友的帳號，以免身分遭到冒用。</li>
                        <li>不應該綁定非法申請的社群帳號（未滿 13 歲無法申請之帳號），以免觸犯法律規定。</li>
                        <li>每種社群平台只能綁定一個帳號，若要變更已經綁定的社群帳號，請先解除綁定後再重新設定！</li>
                    </ul>
                </p>
                <hr>
                @if ($google)
                    <div class="col-md-8">
                        Google 帳號：{{ $google->userId }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#socialite').val('google');
                                         $('#userid').val('{{ $google->userId }}');
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="col-md-8">
                        Google 帳號：
                        <a href="/login/google" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                @if ($facebook)
                    <div class="col-md-8">
                        Facebook 帳號：{{ $facebook->userId }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#socialite').val('facebook');
                                         $('#userid').val('{{ $facebook->userId }}');
										 $('#form').submit();">解除</a>
                    </div>
                @else
                    <div class="col-md-8">
                        Facebook 帳號：
                        <a href="/login/facebook" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                @if ($yahoo)
                    <div class="col-md-8">
                        Yahoo 帳號：{{ $yahoo->userId }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#socialite').val('yahoo');
                                         $('#userid').val('{{ $yahoo->userId }}');
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="col-md-8">
                        Yahoo 帳號：
                        <a href="/login/yahoo" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                @if ($line)
                    <div class="col-md-8">
                        Line 帳號：{{ $line->userId }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#socialite').val('line');
                                         $('#userid').val('{{ $line->userId }}');
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="col-md-8">
                        Line 帳號：
                        <a href="/login/line" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                <form id="form" action="{{ route('socialite.remove') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id='socialite' name='socialite' value="">
                <input type="hidden" id='userid' name='userid' value="">
                </form>
                </div>
            </div>
        </div>
	</div>
</div>
@endsection