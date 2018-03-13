@extends('layouts.dashboard')

@section('page_heading')
班級管理
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
				<select id="grade" name="grade" onchange="location='{{ url()->current() }}?grade=' + $(this).val();">
				@foreach ($grades as $grade)
			    	<option value="{{ $grade }}" {{ $my_grade == $grade ? 'selected' : '' }}>{{ $grade }}年級</option>
			    @endforeach
				</select>
				班級一覽表
			</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>班級代號</th>
						<th>班級名稱</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($classes as $class)
					<tr>
						<form role="form" method="POST" action="{{ route('school.updateClass', [ 'ou' => $class->ou ]) }}">
		    			{{ csrf_field() }}
						<td>
							<label class="form-control">{{ $class->ou }}</label>
						</td>
						<td>
							<input id="description" type="text" class="form-control" name="description" value="{{ $class->description ? $class->description : old('description') }}">
						</td>
						<td>
							<button type="submit" class="btn btn-primary">修改</button>
							<a href="{{ route('school.removeClass', [ 'ou' => $class->ou ]) }}" class="btn btn-danger">刪除</a>
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
			<h4>新增班級</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('school.class') }}">
		    	{{ csrf_field() }}
			    <div class="form-group{{ $errors->has('new-ou') ? ' has-error' : '' }}">
					<label>班級代號</label>
					<input id="new-ou" type="text" class="form-control" name="new-ou" value="{{ $errors->has('new-ou') ? old('new-ou') : '' }}">
					@if ($errors->has('new-ou'))
						<p class="help-block">
							<strong>{{ $errors->first('new-ou') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group{{ $errors->has('new-desc') ? ' has-error' : '' }}">
					<label>班級名稱</label>
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
