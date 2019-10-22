@extends('layouts.dashboard')

@section('page_heading')
<h1 class="page-header">教學科目管理</h1>
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

			<div class="panel panel-default">	  
				<div class="panel-heading">
					<a href="#addSubjectModal" class="btn btn-success" data-toggle="modal" style="float: right;margin-top: 3px;">
						<i class="glyphicon glyphicon-plus-sign"></i><span style="margin-left: 4px;">新增</span>
					</a>
					<h4>教學科目一覽表</h4>
				</div>
				<div class="panel-body">
					<table class="table table-hover" style="margin: 0;">
						<thead>
							<tr>
								<th width="23%">科目代號</th>
								<th width="23%">教學領域</th>
								<th width="31%">科目名稱</th>
								<th width="23%">管理</th>
							</tr>
						</thead>
					</table>
					@if (!empty($subjs))
					@foreach ($subjs as $subj)
						<form role="form" method="POST" action="{{ route('school.updateSubject', [ 'dc' => $dc, 'subject' => $subj['tpSubject'] ]) }}">
						@csrf
						<table class="table table-hover" style="margin: 0;">
							<tr>
								<td width="23%" style="vertical-align: inherit;">
									<label>{{ $subj['tpSubject'] }}</label>
								</td>
								<td width="23%">
									<select class="form-control" name="domain">
									@foreach ($domains as $domain)
										<option value="{{ $domain }}"{{ array_key_exists('tpSubjectDomain', $subj) && $subj['tpSubjectDomain'] == $domain ? ' selected' : '' }}>{{ $domain }}</option>
									@endforeach
									</select>
								</td>
								<td width="31%">
									<input id="description" type="text" class="form-control" name="description" value="{{ $subj['description'] ? $subj['description'] : old('description') }}">
								</td>
								<td width="23%">
									<button type="submit" class="btn btn-primary">修改</button>
									<button type="button" class="btn btn-danger"
										onclick="if(confirm('確定要刪除『'+$(this).parent().parent().find(':text').val()+'』?')){$('#remove-form').attr('action','{{ route('school.removeSubject', [ 'dc' => $dc, 'subject' => $subj['tpSubject'] ]) }}');
												 $('#remove-form').submit();}">刪除</button>
								</td>
							</tr>
						</table>
						</form>
					@endforeach
					@endif
				</div>
			</div>
    
		<form id="remove-form" action="" method="POST" style="display: none;">
		@csrf
		</form>
	</div>

	<div id="addSubjectModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<div class="panel-heading">
						<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
							<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
						</button>
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
</div>
@endsection