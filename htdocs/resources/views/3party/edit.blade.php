@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8" style="margin-left: 20%">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header"><h4>編輯介接專案</h4></div>
                <div class="card-body">
					<form role="form" method="POST" action="{{ route('3party.store') }}">
						@csrf
						<input type="hidden" name="id" value="{{ $project->uuid }}">
						<div class="form-group{{ $errors->has('organization') ? ' has-error' : '' }}">
							<label for="organization">申請單位（機關名稱）</label>
						<input type="text" class="form-control" name="organization" value="{{ $project->organization }}" required>
							@if ($errors->has('organization'))
								<p class="help-block">
									<strong>{{ $errors->first('organization') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group{{ $errors->has('applicationName') ? ' has-error' : '' }}">
							<label for="applicationName">應用平臺名稱（網站服務入口）</label>
							<input type="text" class="form-control" name="applicationName" value="{{ $project->applicationName }}" required>
							@if ($errors->has('applicationName'))
								<p class="help-block">
									<strong>{{ $errors->first('applicationName') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group{{ $errors->has('reason') ? ' has-error' : '' }}">
							<label for="reason">應用平臺之申請背景說明</label>
							<textarea rows="3" cols="40" class="form-control" name="reason" required>{{ $project->reason }}</textarea>
							@if ($errors->has('reason'))
								<p class="help-block">
									<strong>{{ $errors->first('reason') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group{{ $errors->has('website') ? ' has-error' : '' }}">
							<label for="website">應用平台網址</label>
							<input type="text" class="form-control" name="website" value="{{ $project->website }}" required>
							@if ($errors->has('website'))
								<p class="help-block">
									<strong>{{ $errors->first('website') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group{{ $errors->has('redirect') ? ' has-error' : '' }}">
							<label for="redirect">SSO認證後授權碼重導向URL</label>
							<input type="text" class="form-control" name="redirect" value="{{ $project->redirect }}" required>
							@if ($errors->has('redirect'))
								<p class="help-block">
									<strong>{{ $errors->first('redirect') }}</strong>
								</p>
							@endif
						</div>
						<div class="form-group">
							<label for="kind">單位別</label>
							<select class="form-control" name="kind">
								<option value="1"{{ $project->kind == 1 ? ' selected' : '' }}>本局</option>
								<option value="2"{{ $project->kind == 2 ? ' selected' : '' }}>學校</option>
								<option value="3"{{ $project->kind == 3 ? ' selected' : '' }}>廠商</option>
							</select>
						</div>
						<div class="form-group">
							<label>介接業務聯絡窗口</label>
							<div class="form-group{{ $errors->has('connName') ? ' has-error' : '' }}">
								<label for="connName">姓名</label>
								<input type="text" class="form-control" name="connName" value="{{ $project->connName }}" required>
								@if ($errors->has('connName'))
									<p class="help-block">
										<strong>{{ $errors->first('connName') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('connUnit') ? ' has-error' : '' }}">
								<label for="connUnit">部門／單位</label>
								<input type="text" class="form-control" name="connUnit" value="{{ isset($project->connUnit) ? $project->connUnit : '' }}">
								@if ($errors->has('connUnit'))
									<p class="help-block">
										<strong>{{ $errors->first('connUnit') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('connEmail') ? ' has-error' : '' }}">
								<label for="connEmail">EMAIL</label>
								<input type="email" class="form-control" name="connEmail" value="{{ $project->connEmail }}" required>
								@if ($errors->has('connEmail'))
									<p class="help-block">
										<strong>{{ $errors->first('connEmail') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('connTel') ? ' has-error' : '' }}">
								<label for="connTel">電話</label>
								<input type="text" class="form-control" name="connTel" value="{{ $project->connTel }}" pattern="^([0-9]{10}|[0-9]{9}|[0-9]{8}|[0-9]{7})$" required>
								@if ($errors->has('connTel'))
									<p class="help-block">
										<strong>{{ $errors->first('connTel') }}</strong>
									</p>
								@endif
							</div>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-success">儲存並更新內容</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
