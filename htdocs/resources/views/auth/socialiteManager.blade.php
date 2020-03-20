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
                        <li>不應該綁定父母或朋友的帳號，以免身份遭到冒用。</li>
                        <li>不應該綁定非法申請的社群帳號（未滿 13 歲無法申請之帳號），以免觸犯法律規定。</li>
                        <li>每種社群平台只能綁定一個帳號，若要變更已經綁定的社群帳號，請先解除綁定後再重新設定！</li>
                    </ul>
                </p>
                <hr>
                @if ($google)
                    <div class="col-md-8">
                        Google 帳號：{{ $google->userID }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('Google');
                                         $('#userid').value($google->userID);
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="col-md-8">
                        Google 帳號：
                        <a href="/login/Google" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                @if ($facebook)
                    <div class="col-md-8">
                        Facebook 帳號：{{ $facebook->userID }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('facebook');
                                         $('#userid').value($facebook->userID);
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
                        Yahoo 帳號：{{ $yahoo->userID }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('Yahoo');
                                         $('#userid').value($yahoo->userID);
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="col-md-8">
                        Yahoo 帳號：
                        <a href="/login/Yahoo" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                @if ($yahoo)
                    <div class="col-md-8">
                        Line 帳號：{{ $line->userID }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('line');
                                         $('#userid').value($line->userID);
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="col-md-8">
                        Line 帳號：
                        <a href="/login/line" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                <form id="form" action="" method="" style="display: none;">
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