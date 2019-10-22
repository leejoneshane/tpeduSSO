@extends('layouts.userboard')

@section('page_heading')
@endsection

@section('section')
<div class="container">
    <div class="row justify-content-center">
	<div class="col-md-8 col-md-offset-2">
	    <div class="card card-default" style="margin-top: 20px">
		<div class="card-header">由第三方身分驗證系統登入之個人資料登錄</div>
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
			<p>第一次由{{ $source ?? '第三方' }} 帳號登入，請輸入你的個人資料！</p>
		<form class="form-horizontal" method="POST" action="{{ route('registerThird') }}">
		    {{ csrf_field() }}
			<input id="sourceFrom" type="hidden"  name="sourceFrom" value="{{ $source ?? '第三方' }}" > 
			<input id="token" type="hidden"  name="token" value="{{ $socialite->token}}" > 
			<input id="id" type="hidden"  name="id" value="{{ $socialite->id }}" > 
		    <div class="form-group{{ $errors->has('idno') ? ' has-error' : '' }}">
			<label for="idno" class="col-md-4 control-label">身分證字號</label>
			<div class="col-md-6">
			    <input id="idno" type="text" class="form-control" name="idno"  placeholder="身分證字號或居留證" style="ime-mode:disabled" value="{{ old('idno') ?? '' }}" required>
			    @if ($errors->has('idno'))
				<span class="help-block">
				<strong>{{ $errors->first('idno') }}</strong>
				</span>
			    @endif
			</div>
		    </div>
		    <div class="form-group{{ $errors->has('displayName') ? ' has-error' : '' }}">
			<label for="displayName" class="col-md-4 control-label">姓名</label>
			<div class="col-md-6">
			    <input id="displayName" type="text" class="form-control" name="displayName" value="{{ old('displayName') ?? $socialite->name}}" required> 
			    @if ($errors->has('displayName'))
			    <span class="help-block">
				<strong>{{ $errors->first('displayName') }}</strong>
			    </span>
			    @endif
			</div>
		    </div>
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
			<label for="email" class="col-md-4 control-label">eMail信箱</label>
			<div class="col-md-6">
			    <input id="email" type="text" class="form-control" name="email" value="{{ old('email') ?? $socialite->email }}" style="ime-mode:disabled" required> 
			    @if ($errors->has('email'))
			    <span class="help-block">
				<strong>{{ $errors->first('email') }}</strong>
			    </span>
			    @endif
			</div>
		    </div>
            <div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
			<label for="mobile" class="col-md-4 control-label">電話號碼</label>
			<div class="col-md-6">
			    <input id="mobile" type="text" class="form-control" name="mobile" value="{{ old('mobile') ?? '' }}" style="ime-mode:disabled" > 
			    @if ($errors->has('mobile'))
			    <span class="help-block">
				<strong>{{ $errors->first('mobile') }}</strong>
			    </span>
			    @endif
			</div>
		    </div>          
		    <div class="form-group">
			<div class="col-md-8 col-md-offset-4">
			    <button type="submit" class="btn btn-primary">
				確定
			    </button>
			</div>
		    </div>
		</form>
	        </div>
	    </div>
	</div>
    </div>
</div>
@endsection