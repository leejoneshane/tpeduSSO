@extends('layouts.dashboard')

@section('page_heading')
職稱管理
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
	<div class="col-sm-6">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>
				<select id="ou" name="ou" onchange="location='{{ url()->current() }}?ou=' + $(this).val();">
				@foreach ($ous as $ou => $desc)
			    	<option value="{{ $ou }}" {{ $my_ou == $ou ? 'selected' : '' }}>{{ $desc }}</option>
			    @endforeach
				</select>
				職務一覽表
			</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>職務代號</th>
						<th>職稱</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($roles as $role)
					<tr>
						<form role="form" method="POST" action="{{ route('school.updateRole', [ 'role' => $role->cn ]) }}">
		    			{{ csrf_field() }}
						<input type="hidden" name="ou" value="{{ $my_ou }}">
						<td>
							<input id="role" type="text" class="form-control" name="role" value="{{ $role->cn ? $role->cn : old('role') }}">
						</td>
						<td>
							<input id="description" type="text" class="form-control" name="description" value="{{ $role->description ? $role->description : old('description') }}">
						</td>
						<td>
							<button type="submit" class="btn btn-primary">修改</button>
							<a href="{{ route('school.removeRole', [ 'role' => $role->cn ]) }}?ou={{ $my_ou }}" class="btn btn-danger">刪除</a>
						</td>
						</form>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>新增職務</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('school.role') }}">
		    	{{ csrf_field() }}
				<input type="hidden" name="ou" value="{{ $my_ou }}">
			    <div class="form-group{{ $errors->has('new-role') ? ' has-error' : '' }}">
					<label>職務代號</label>
					<input id="new-role" type="text" class="form-control" name="new-role" value="{{ $errors->has('new-role') ? old('new-role') : '' }}">
					@if ($errors->has('new-role'))
						<p class="help-block">
							<strong>{{ $errors->first('new-role') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('new-desc') ? ' has-error' : '' }}">
					<label>職稱</label>
					<input id="new-desc" type="text" class="form-control" name="new-desc" value="{{ $errors->has('new-desc') ? old('new-desc') : '' }}">
					@if ($errors->has('new-desc'))
						<p class="help-block">
							<strong>{{ $errors->first('new-desc') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<button type="submit" class="btn btn-success">新增</button>
				</div>
			</form>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection
