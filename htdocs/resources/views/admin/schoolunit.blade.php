@extends('layouts.dashboard')

@section('page_heading')
行政部門管理
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
			<h4>行政部門一覽表</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>處室代號</th>
						<th>處室全銜</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@if ($ous)
					@foreach ($ous as $ou)
					<tr>
						<form id="{{ $ou->ou }}form" method="POST" action="{{ route('school.updateUnit', [ 'dc' => $dc, 'ou' => $ou->ou ]) }}">
		    			@csrf
						<td>
							<input id="{{ $ou->ou }}ou" type="text" class="form-control" name="ou" value="{{ $ou->ou ? $ou->ou : old('ou') }}">
						</td>
						<td>
							<input id="{{ $ou->ou }}description" type="text" class="form-control" name="description" value="{{ $ou->description ? $ou->description : old('description') }}">
						</td>
						<td>
							<button type="submit" class="btn btn-primary">修改</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#remove-form').attr('action','{{ route('school.removeUnit', [ 'dc' => $dc, 'ou' => $ou->ou ]) }}');
										 $('#remove-form').submit();">刪除</button>
						</td>
						</form>
					</tr>
					@endforeach
					@endif
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
			<h4>新增行政部門</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('school.createUnit', [ 'dc' => $dc ]) }}">
		    	@csrf
			    <div class="form-group{{ $errors->has('new-ou') ? ' has-error' : '' }}">
					<label>處室代號</label>
					<input id="new-ou" type="text" pattern="[0-9a-z]{3,}" class="form-control" name="new-ou" value="{{ $errors->has('new-ou') ? old('new-ou') : '' }}" placeholder="請使用英文字母＋數字，至少三個字。" required>
					@if ($errors->has('new-ou'))
						<p class="help-block">
							<strong>{{ $errors->first('new-ou') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('new-desc') ? ' has-error' : '' }}">
					<label>處室全銜</label>
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
