@extends('layouts.app')

@section('content')
<div class="container" style="margin-top: 20px">
    <div class="row justify-content-center">
	<div class="col-md-8 col-md-offset-2">
	    <div class="card card-default">
		<div class="card-header">設定學校管理員</div>
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
		<form class="form-horizontal" method="POST" action="{{ route('schoolAdminRemove') }}">
		    {{ csrf_field() }}
		    <input type="hidden" name="dc" value="{{ $dc }}">
		    <div class="form-group">
		    <label for="current-account" class="col-md-8 control-label">刪除管理員</label>
		    <div class="col-md-6">
			<select class="form-control" id="delete-admin" name="delete-admin">
			    <option value=""></option>
			@foreach ($admins as $admin)
			    <option value="{{ $admin->idno }}">{{ $admin->name }}</option>
			@endforeach
			</select>
			@if ($errors->has('delete-admin'))
			    <span class="help-block">
			        <strong>{{ $errors->first('delete-admin') }}</strong>
			    </span>
			@endif
		    </div>
		    </div>
		    
		    <div class="form-group">
			<div class="col-md-6 col-md-offset-4">
			    <button type="submit" class="btn btn-danger">
				刪除
			    </button>
			</div>
		    </div>
		</form>
		
		<form class="form-horizontal" method="POST" action="{{ route('schoolAdmin') }}">
		    {{ csrf_field() }}
		    <input type="hidden" name="dc" value="{{ $dc }}">
		    <div class="form-group{{ $errors->has('new-admin') ? ' has-error' : '' }}">
			<label for="new-admin" class="col-md-8 control-label">新增管理員</label>
			<div class="col-md-6">
			    <input id="new-admin" type="text" class="form-control" name="new-admin" placeholder="請輸入身分證字號">
			    @if ($errors->has('new-admin'))
				<span class="help-block">
				    <strong>{{ $errors->first('new-admin') }}</strong>
				</span>
			    @endif
			</div>
		    </div>

		    <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }}">
			<label for="new-password" class="col-md-8 control-label">變更管理密碼</label>
			<div class="col-md-6">
			    <input id="new-password" type="password" class="form-control" name="new-password" placeholder="若第一次登入，務必更新密碼！">
			    @if ($errors->has('new-password'))
			    <span class="help-block">
				<strong>{{ $errors->first('new-password') }}</strong>
			    </span>
			    @endif
			</div>
		    </div>

		    <div class="form-group">
			<label for="new-password-confirm" class="col-md-8 control-label">確認管理密碼</label>
			<div class="col-md-6">
			    <input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" placeholder="再輸入一次相同密碼">
			</div>
		    </div>

		    <div class="form-group">
			<div class="col-md-6 col-md-offset-4">
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
