@extends('layouts.dashboard')

@section('page_heading')
設定管理員
@endsection

@section('section')
<div class="container">
    <div class="row">
	<div class="col-md-4 col-md-offset-2">
		<div>
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
		<form role="form" method="POST" action="{{ route('schoolAdminRemove') }}">
		    {{ csrf_field() }}
		    <input type="hidden" name="dc" value="{{ $dc }}">
		    <div class="form-group">
		    <label>刪除管理員</label>
			<select class="form-control" id="delete-admin" name="delete-admin">
			    <option value=""></option>
			@foreach ($admins as $admin)
			    <option value="{{ $admin }}">{{ $admin }}</option>
			@endforeach
			</select>
			@if ($errors->has('delete-admin'))
			    <p class="help-block">
			        <strong>{{ $errors->first('delete-admin') }}</strong>
			    </p>
			@endif
		    </div>
		    
		    <div class="form-group">
			    <button type="submit" class="btn btn-danger">刪除</button>
		    </div>
		</form>
		
		<form role="form" method="POST" action="{{ route('schoolAdmin') }}">
		    {{ csrf_field() }}
		    <input type="hidden" name="dc" value="{{ $dc }}">
		    <div class="form-group{{ $errors->has('new-admin') ? ' has-error' : '' }}">
			<label>新增管理員</label>
			<input id="new-admin" type="text" class="form-control" name="new-admin" placeholder="請輸入身分證字號">
			@if ($errors->has('new-admin'))
				<p class="help-block">
					<strong>{{ $errors->first('new-admin') }}</strong>
				</p>
			@endif
		    </div>

		    <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }}">
			<label>變更管理密碼</label>
			<input id="new-password" type="password" class="form-control" name="new-password" placeholder="若第一次登入，務必更新密碼！">
			@if ($errors->has('new-password'))
				<p class="help-block">
					<strong>{{ $errors->first('new-password') }}</strong>
				</p>
			@endif
		    </div>

		    <div class="form-group">
			<label>確認管理密碼</label>
			<input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" placeholder="再輸入一次相同密碼">
		    </div>

		    <div class="form-group">
			    <button type="submit" class="btn btn-primary">確定</button>
		    </div>
		</form>
	    </div>
	</div>
    </div>
</div>
@endsection
