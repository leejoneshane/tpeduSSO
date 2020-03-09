@extends('layouts.superboard')

@section('page_heading')
審核介接專案
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
			<h4>審核介接專案</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('bureau.denyProject', [ 'uuid' => $project->uuid ]) }}">
			@csrf
			    <div class="form-group">
					<label for="organization">申請單位（機關名稱）：{{ isset($project->organization) ?: '' }}</label>
				</div>
			    <div class="form-group">
					<label for="applicationName">應用平臺名稱（網站服務入口）：{{ isset($project->applicationName) ?: '' }}</label>
				</div>
			    <div class="form-group">
					<label for="reason">應用平臺之申請背景說明：{{ isset($project->reason) ?: '' }}</label>
				</div>
			    <div class="form-group">
					<label for="website">應用平台網址：{{ isset($project->website) ?: '' }}</label>
				</div>
			    <div class="form-group">
					<label for="redirect">SSO認證後授權碼重導向URL：{{ isset($project->redirect) ?: '' }}</label>
				</div>
			    <div class="form-group">
					<label for="redirect">特權專案：{{ isset($project->privileged) && $project->privileged ? '是' : '否' }}</label>
				</div>
			    <div class="form-group">
					<label style="display:block">審核意見</label>
					<textarea rows="3" cols="40" class="form-control" name="reason"></textarea>
				</div>
			    <div class="form-group">
					<button type="submit" class="btn btn-success">通知專案申請人</button>
				</div>
			</form>
		</div>
	</div>
	</div>
</div>
@endsection
