@extends('layouts.userboard')

@section('page_heading')
@endsection

@section('section')
<div class="container">
    <div class="row justify-content-center">
	<div class="col-md-8 col-md-offset-2">
	    <div class="card card-default" style="margin-top: 20px">
		<div class="card-header">變更（建立）帳號</div>
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
		<p>注意：為了避免帳號遭到盜用，請勿繼續使用預設帳號與預設密碼！</p>
		<form class="form-horizontal" method="POST" action="{{ route('changeAccount') }}">
		    {{ csrf_field() }}
			@if ($is_change_account!='1')
		    <div class="form-group{{ $errors->has('new-account') ? ' has-error' : '' }}">
			<label for="new-account" class="col-md-4 control-label">新帳號</label>
			<div class="col-md-6">
			    <input id="new-account" type="password" class="form-control" name="new-account" required> <input id=show-account type=checkbox ><label for="show-account">顯示帳號</label>
			    @if ($errors->has('new-account'))
			    <span class="help-block">
				<strong>{{ $errors->first('new-account') }}</strong>
			    </span>
			    @endif


			</div>
		    </div>
			<!--
		    <div class="form-group">
			<div for="new-account-confirm" class="col-md-8">請再輸入一次新帳號</div>
			<div class="col-md-8">
			    <input id="new-account-confirm" type="password" class="form-control" name="new-account_confirmation" required>
			</div>
		    </div>
			-->
		    <div class="form-group">
			<div class="col-md-8 col-md-offset-4">
			    <button type="submit" class="btn btn-primary">
				確定
			    </button>
			</div>
		    </div>
			@endif
			@if ($is_change_account=='1')
		    <div class="alert alert-danger">
			@if (isset($error))
				{{ $error }}
			@else
				您的帳號已經變更完成，限變更一次!!
			@endif
		    </div>
			@endif			
		</form>
	        </div>
	    </div>
	</div>
    </div>
</div>
@endsection
@section('script')
@parent
<script type="text/javascript">
$(document).ready(function(){
	$('#show-account').click(function(){
		if ($("#show-account").prop("checked")){
				$("#new-account").prop("type","text");
			}else{
				$("#new-account").prop("type","password");
			}
	});
});
</script>
@endsection

