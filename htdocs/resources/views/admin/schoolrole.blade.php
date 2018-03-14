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
						<form role="form" method="POST" action="{{ route('school.updateRole', [ 'ou' => $my_ou, 'role' => $role->cn ]) }}">
		    			@csrf
						<td>
							<input id="role" type="text" class="form-control" name="role" value="{{ $role->cn ? $role->cn : old('role') }}">
						</td>
						<td>
							<input id="description" type="text" class="form-control" name="description" value="{{ $role->description ? $role->description : old('description') }}">
						</td>
						<td>
							<button type="submit" class="btn btn-primary">修改</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#remove-form').attr('action','{{ route('school.removeRole', [ 'ou' => $my_ou, 'role' => $role->cn ]) }}');
										 $('#remove-form').submit();">刪除</button>
						</td>
						</form>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		</div>
	</div>

    <form id="remove-form" action="" method="POST" style="display: none;">
    @csrf
    </form>

	<div class="col-sm-6">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>新增職務</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('school.createRole', [ 'ou' => $my_ou ]) }}">
		    	@csrf
			    <div class="form-group{{ $errors->has('new-role') ? ' has-error' : '' }}">
					<label>職務代號</label>
					<input id="new-role" type="text" class="form-control" name="new-role" value="{{ $errors->has('new-role') ? old('new-role') : '' }}" required>
					@if ($errors->has('new-role'))
						<p class="help-block">
							<strong>{{ $errors->first('new-role') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('new-desc') ? ' has-error' : '' }}">
					<label>職稱</label>
					<input id="new-desc" type="text" class="form-control" name="new-desc" value="{{ $errors->has('new-desc') ? old('new-desc') : '' }}" required>
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
