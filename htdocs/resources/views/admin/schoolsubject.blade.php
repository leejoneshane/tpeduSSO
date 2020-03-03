@extends('layouts.dashboard')

@section('page_heading')
教學科目管理
@endsection

@section('section')
<div class="container">
	<div class="row">
	@if (session('error'))
	    <div class="col-sm-6 alert alert-danger">
		{{ session('error') }}
	    </div>
	@endif
	@if (session('success'))
	    <div class="col-sm-6 alert alert-success">
		{{ session('success') }}
	    </div>
	@endif
	<div class="col-sm-6">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>教學科目一覽表</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>科目代號</th>
						<th>教學領域</th>
						<th>科目名稱</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@if (!empty($subjs))
					@foreach ($subjs as $subj)
					<tr>
						<form role="form" method="POST" action="{{ route('school.updateSubject', [ 'dc' => $dc, 'subject' => $subj['tpSubject'] ]) }}">
		    			@csrf
						<td style="vertical-align: inherit;">
							<label>{{ $subj['tpSubject'] }}</label>
						</td>
						<td>
							<select class="form-control" name="domain">
							@foreach ($domains as $domain)
			    				<option value="{{ $domain }}"{{ array_key_exists('tpSubjectDomain', $subj) && $subj['tpSubjectDomain'] == $domain ? ' selected' : '' }}>{{ $domain }}</option>
							@endforeach
							</select>
						</td>
						<td>
							<input id="description" type="text" class="form-control" name="description" value="{{ $subj['description'] ? $subj['description'] : old('description') }}">
						</td>
						<td>
							<button type="submit" class="btn btn-primary">修改</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#remove-form').attr('action','{{ route('school.removeSubject', [ 'dc' => $dc, 'subject' => $subj['tpSubject'] ]) }}');
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
			<h4>新增科目</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('school.createSubject', [ 'dc' => $dc ]) }}">
		    	@csrf
			    <div class="form-group{{ $errors->has('new-subj') ? ' has-error' : '' }}">
					<label>科目代號</label>
					<input id="new-subj" type="text" class="form-control" name="new-subj" value="{{ $errors->has('new-subj') ? old('new-subj') : '' }}" required>
					@if ($errors->has('new-subj'))
						<p class="help-block">
							<strong>{{ $errors->first('new-subj') }}</strong>
						</p>
					@endif
				</div>
			    <div class="form-group">
					<label>教學領域</label>
					<select class="form-control" name="new-dom">
					@foreach ($domains as $domain)
			    		<option value="{{ $domain }}">{{ $domain }}</option>
					@endforeach
					</select>
				</div>
			    <div class="form-group{{ $errors->has('new-desc') ? ' has-error' : '' }}">
					<label>科目名稱</label>
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
