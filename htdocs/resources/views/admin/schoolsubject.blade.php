@extends('layouts.dashboard')

@section('page_heading')
教學科目管理
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
			<h4>教學科目一覽表</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>科目代號</th>
						<th>教學領域</th>
						<th>科目名稱</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($subjs as $subj)
					<tr>
						<form role="form" method="POST" action="{{ route('school.updateSubject', [ 'subject' => $subj->subject ]) }}">
		    			@csrf
						<td style="vertical-align: inherit;">
							<label>{{ $subj->subject }}</label>
						</td>
						<td>
							<select class="form-control" name="domain">
								<option value="語文"{{ $subj->domain == '語文' ? ' selected' : '' }}>語文</option>
								<option value="數學"{{ $subj->domain == '數學' ? ' selected' : '' }}>數學</option>
								<option value="社會"{{ $subj->domain == '社會' ? ' selected' : '' }}>社會</option>
								<option value="自然科學"{{ $subj->domain == '自然科學' ? ' selected' : '' }}>自然科學</option>
								<option value="藝術"{{ $subj->domain == '藝術' ? ' selected' : '' }}>藝術</option>
								<option value="綜合活動"{{ $subj->domain == '綜合活動' ? ' selected' : '' }}>綜合活動</option>
								<option value="科技"{{ $subj->domain == '科技' ? ' selected' : '' }}>科技</option>
								<option value="健康與體育"{{ $subj->domain == '健康與體育' ? ' selected' : '' }}>健康與體育</option>
							</select>
						</td>
						<td>
							<input id="description" type="text" class="form-control" name="description" value="{{ $subj->description ? $subj->description : old('description') }}">
						</td>
						<td>
							<button type="submit" class="btn btn-primary">修改</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#remove-form').attr('action','{{ route('school.removeSubject', [ 'subject' => $subj->subject ]) }}');
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
			<h4>新增科目</h4>
		</div>
		<div class="panel-body">
			<form role="form" method="POST" action="{{ route('school.createSubject') }}">
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
			    <div class="form-group{{ $errors->has('new-dom') ? ' has-error' : '' }}">
					<label>教學領域</label>
					<select class="form-control" name="new-dom">
						<option value="語文">語文</option>
						<option value="數學">數學</option>
						<option value="社會">社會</option>
						<option value="自然科學">自然科學</option>
						<option value="藝術">藝術</option>
						<option value="綜合活動">綜合活動</option>
						<option value="科技">科技</option>
						<option value="健康與體育">健康與體育</option>
					</select>
					@if ($errors->has('new-dom'))
						<p class="help-block">
							<strong>{{ $errors->first('new-dom') }}</strong>
						</p>
					@endif
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
