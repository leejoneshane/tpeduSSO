@extends('layouts.admin')

@section('page_heading')
@endsection

@section('section')
<div class="container">
    <div class="row justify-content-center">

		@if (!empty($policy) && !session('policy') && $policyYn == 'N')
		<div id="policyDiv" class="col-md-8 col-md-offset-2">
			<div class="card card-default" style="margin-top: 20px">
				<div class="card-header">家長個資及服務條款聲明</div>
				<div class="card-body">
					<div id="policytextdiv" style="max-height: 370px;overflow: auto;white-space: pre-wrap;">
{!! $policy !!}
					</div>
					<div style="margin: 15px;text-align: center;">
						<button type="button" id="agree" class="btn btn-primary" onclick="agree()" style="margin: 0 15px;width: calc(50% - 32px);max-width: 140px;">同意</button>
						<button type="button" id="disagree" class="btn btn-danger" onclick="disagree()" style="margin: 0 15px;width: calc(50% - 32px);max-width: 140px;">不同意</button>
					</div>
				</div>
			</div>
		</div>
		@endif

		@if (!empty($policy) && !session('policy') && $policyYn == 'N')
		<div id="inputDiv" class="col-md-8 col-md-offset-2" style="display: none;">
		@else
		<div id="inputDiv" class="col-md-8 col-md-offset-2">	
		@endif
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
					<p>第一次由{{ $source ?? '第三方' }}帳號登入，請輸入您的真實個人資料！</p>
					@if(isset($limitDays))
					<p>提醒您帳號建立後，必須在{{$limitDays}}天內透過親子連結至少連結一位您的小孩，超過時間或輸入非真實資料，本系統將刪除您登錄的帳號。</p>
					@endif
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
						<input id="displayName" type="text" class="form-control" name="displayName" value="{{ old('displayName') ?? ''}}" required> 
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
						<input id="mobile" type="text" class="form-control" name="mobile" value="{{ old('mobile') ?? '' }}" style="ime-mode:disabled" required> 
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
					<input type="hidden" id="policyYn" name="policyYn" value="{{ $policyYn }}" />
				</form>
				</div>
			</div>
		</div>
    </div>

	<div id="askagree" class="modal fade">
		<div class="modal-dialog" style="display: -ms-flexbox;display: flex;-ms-flex-align: center;align-items: center;min-height: calc(100% - (.5rem * 12));">
			<div class="modal-content" style="width: 100%;">
				<div class="panel-default">
					<div class="panel-body">
						<div>如果你不同意個資與服務條款聲明，將無法完成第三方帳戶(Google/Facebook/Yahoo)與本系統帳戶的連結作業。你也無法使用本系統提供的家長服務或其他系統服務。</div>
						<div>請問你是否確定不同意家長個資與服務條款聲明？</div>
					</div>
					<div style="text-align: center;">
						<button type="button" data-dismiss="modal" aria-label="Close" style="margin: 15px;" class="btn btn-default">回服務條款聲明</button>
						<button type="button" style="margin: 15px;" class="btn btn-default" onclick="window.location.href='/'">是的，我不同意</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	function agree() {
		$("#policyDiv").fadeOut("fast",function(){
			$("#inputDiv").fadeIn("fast",function(){
				$("#policyYn").val('Y');
			});
		});
	}

	function disagree() {
		$("#askagree").modal({backdrop: 'static', keyboard: false}).modal('show');
	}
</script>
@endsection