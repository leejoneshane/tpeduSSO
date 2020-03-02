@extends('layouts.app')

@section('content')
<div class="container">
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
                        <a href="{{ route('login.google') }}" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                @if ($facebook)
                    <div class="col-md-8">
                        Facebook 帳號：{{ $facebook->userID }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('Facebook');
                                         $('#userid').value($facebook->userID);
										 $('#form').submit();">解除</a>
                    </div>
                @else
                    <div class="col-md-8">
                        Facebook 帳號：
                        <a href="{{ route('login.facebook') }}" class="btn btn-primary">綁定</a>
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
                        <a href="{{ route('login.yahoo') }}" class="btn btn-primary">綁定</a>
                    </div>
                @endif
                @if ($yahoo)
                    <div class="col-md-8">
                        Line 帳號：{{ $line->userID }}
                        <button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('socialite.remove') }}');
                                         $('#form').attr('method', 'POST');
                                         $('#socialite').value('Line');
                                         $('#userid').value($line->userID);
										 $('#form').submit();">解除</button>
                    </div>
                @else
                    <div class="col-md-8">
                        Line 帳號：
                        <a href="{{ route('login.line') }}" class="btn btn-primary">綁定</a>
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