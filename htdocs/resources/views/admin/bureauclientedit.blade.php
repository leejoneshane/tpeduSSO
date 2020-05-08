@extends('layouts.superboard')

@section('page_heading')
編輯 OAuth 用戶端
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
	<div class="col-sm-8" style="margin-left:18%">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>編輯 OAuth 用戶端</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('bureau.updateClient', [ 'uuid' => $project->uuid ]) }}">
			@csrf
				<input type="hidden" name="id" value="{{ $project->uuid }}">
			    <div class="form-group{{ $errors->has('applicationName') ? ' has-error' : '' }}">
					<label for="applicationName">應用平臺名稱</label>
					<input type="text" class="form-control" name="applicationName" value="{{ isset($project->applicationName) ? $project->applicationName : '' }}" placeholder="用於顯示在授權頁面，讓使用者得知：何種應用平臺透過單一身分驗證服務進行身分認證" required>
					@if ($errors->has('applicationName'))
						<p class="help-block">
							<strong>{{ $errors->first('applicationName') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('redirect') ? ' has-error' : '' }}">
					<label for="redirect">授權碼回傳網址</label>
					<input type="text" class="form-control" name="redirect" value="{{ isset($project->redirect) ? $project->redirect : '' }}" required>
					@if ($errors->has('redirect'))
						<p class="help-block">
							<strong>{{ $errors->first('redirect') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<label for="secret">更換密鑰（使用舊密鑰的應用程式將無法介接成功，系統遷移或遺失密鑰時使用！）</label>
					<input type="checkbox" class="form-control checkbox" name="secret" value="1">
				</div>
			    <div class="form-group">
					<button type="submit" class="btn btn-success">儲存</button>
				</div>
			</form>
		</div>
	</div>
	</div>
</div>
@endsection
