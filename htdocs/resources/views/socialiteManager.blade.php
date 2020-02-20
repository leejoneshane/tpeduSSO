@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-default" style="margin-top: 20px">
            <div class="card-header">社群帳號綁定</div>

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

                <p>每種社群平台只能綁定一個帳號，若要變更已經綁定的社群帳號，請先解除綁定後再重新設定！</p>
                @if ($google)
                    <div class="row">
                        已綁定 Google 帳號：{{ $google->userID }}。
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('Google');
                                         $('#userid').value($google->userID);
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="row">
                        請綁定 Google 帳號：
                        <button type="button" style="background-image:url({{ asset('img/google.png') }});width:50px;height:50px;" onclick="location='{{ route('login.google') }}';">
                    </div>
                @endif
                @if ($facebook)
                    <div class="row">
                        已綁定 Facebook 帳號：{{ $facebook->userID }}。
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('Facebook');
                                         $('#userid').value($facebook->userID);
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="row">
                        請綁定 Facebook 帳號：
                        <button type="button" style="background-image:url({{ asset('img/facebook.png') }});width:50px;height:50px;" onclick="location='{{ route('login.facebook') }}';">
                    </div>
                @endif
                @if ($yahoo)
                    <div class="row">
                        已綁定 Yahoo 帳號：{{ $yahoo->userID }}。
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('Yahoo');
                                         $('#userid').value($yahoo->userID);
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="row">
                        請綁定 Yahoo 帳號：
                        <button type="button" style="background-image:url({{ asset('img/yahoo.png') }});width:50px;height:50px;" onclick="location='{{ route('login.yahoo') }}';">
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
@endsection