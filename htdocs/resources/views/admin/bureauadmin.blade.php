@extends('layouts.superboard')

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
		@if (count($admins)>0)
		<form role="form" method="POST" action="{{ route('bureau.removeAdmin') }}">
		    {{ csrf_field() }}
		    <div class="form-group">
		    <label>刪除管理員</label>
			<select class="form-control" id="delete-admin" name="delete-admin">
			    <option value=""></option>
			@foreach ($admins as $admin)
			    <option value="{{ $admin->idno }}">{{ $admin->name }}</option>
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
		@endif
		
		<form role="form" method="POST" action="{{ route('bureau.createAdmin') }}">
		    {{ csrf_field() }}
		    <div class="form-group{{ $errors->has('new-admin') ? ' has-error' : '' }}">
			<label>新增管理員</label>
			<input id="new-admin" type="text" class="form-control" name="new-admin" placeholder="請輸入身分證字號">
			@if ($errors->has('new-admin'))
				<p class="help-block">
					<strong>{{ $errors->first('new-admin') }}</strong>
				</p>
			@endif
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
