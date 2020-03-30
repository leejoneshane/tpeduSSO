@extends('layouts.superboard')

@section('page_heading')
{{ isset($project) ? '編輯' : '新增' }}介接專案
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
			<h4>{{ isset($project) ? '編輯' : '新增' }}介接專案</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('bureau.storeProject') }}">
			@csrf
			@if (isset($project))
				<input type="hidden" name="uuid" value="{{ $project->uuid }}">
			@endif
			    <div class="form-group{{ $errors->has('organization') ? ' has-error' : '' }}">
				<label for="organization">申請單位（機關名稱）</label>
				<input type="text" class="form-control" name="organization" value="{{ isset($project->organization) ? $project->organization : '' }}" required>
					@if ($errors->has('organization'))
						<p class="help-block">
							<strong>{{ $errors->first('organization') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('applicationName') ? ' has-error' : '' }}">
					<label for="applicationName">應用平臺名稱（網站服務入口）</label>
					<input type="text" class="form-control" name="applicationName" value="{{ isset($project->applicationName) ? $project->applicationName : '' }}" placeholder="用於顯示在授權頁面，讓使用者得知：何種應用平臺透過單一身份驗證服務進行身份認證" required>
					@if ($errors->has('applicationName'))
						<p class="help-block">
							<strong>{{ $errors->first('applicationName') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('reason') ? ' has-error' : '' }}">
					<label for="reason">應用平臺之申請背景說明</label>
					<textarea rows="3" cols="40" class="form-control" name="reason" placeholder="就欲申請介接之目的、未來規劃…等，簡述說明。如：「大學申請入學─備審資料數位化系統」，做為學生參加大學甄試所需之學習履歷相關資料" required>{{ isset($project->reason) ? $project->reason : '' }}</textarea>
					@if ($errors->has('reason'))
						<p class="help-block">
							<strong>{{ $errors->first('reason') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('website') ? ' has-error' : '' }}">
					<label for="website">應用平台網址</label>
					<input type="text" class="form-control" name="website" value="{{ isset($project->website) ? $project->website : '' }}" required>
					@if ($errors->has('website'))
						<p class="help-block">
							<strong>{{ $errors->first('website') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('redirect') ? ' has-error' : '' }}">
					<label for="redirect">SSO認證後授權碼重導向URL</label>
					<input type="text" class="form-control" name="redirect" value="{{ isset($project->redirect) ? $project->redirect : '' }}" required>
					@if ($errors->has('redirect'))
						<p class="help-block">
							<strong>{{ $errors->first('redirect') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<label for="privileged">特權專案</label>
					<input type="checkbox" class="form-control checkbox" name="privileged" value="1"{{ isset($project->privileged) && $project->privileged ? ' checked' : '' }}>
				</div>
			    <div class="form-group">
					<label for="kind">單位別</label>
					<select class="form-control" name="kind">
			    		<option value="1"{{ isset($project->kind) && $project->kind == 1 ? ' selected' : '' }}>本局</option>
			    		<option value="2"{{ isset($project->kind) && $project->kind == 2 ? ' selected' : '' }}>學校</option>
			    		<option value="3"{{ isset($project->kind) && $project->kind == 3 ? ' selected' : '' }}>廠商</option>
					</select>
				</div>
		    	<div class="form-group">
					<label>介接業務聯絡窗口</label>
				    <div class="form-group{{ $errors->has('connName') ? ' has-error' : '' }}">
						<label for="connName">姓名</label>
						<input type="text" class="form-control" name="connName" value="{{ isset($project->connName) ? $project->connName : '' }}" required>
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
						<input type="email" class="form-control" name="connEmail" value="{{ isset($project->connEmail) ? $project->connEmail : '' }}" required>
						@if ($errors->has('connEmail'))
							<p class="help-block">
								<strong>{{ $errors->first('connEmail') }}</strong>
							</p>
						@endif
					</div>
				    <div class="form-group{{ $errors->has('connTel') ? ' has-error' : '' }}">
						<label for="connTel">電話</label>
						<input type="text" class="form-control" name="connTel" value="{{ isset($project->connTel) ? $project->connTel : '' }}" pattern="^([0-9]{10}|[0-9]{9}|[0-9]{8}|[0-9]{7})$" required>
						@if ($errors->has('connTel'))
							<p class="help-block">
								<strong>{{ $errors->first('connTel') }}</strong>
							</p>
						@endif
					</div>
				</div>
			    <div class="form-group{{ $errors->has('memo') ? ' has-error' : '' }}">
					<label style="display:block">備註</label>
					<textarea rows="3" cols="40" class="form-control" name="memo">{{ isset($project->memo) ? $project->memo : '' }}</textarea>
				</div>
			    <div class="form-group">
					<button type="submit" class="btn btn-success">{{ isset($project) ? '儲存' : '新增' }}</button>
				</div>
			</form>
		</div>
	</div>
	</div>
</div>
@endsection
