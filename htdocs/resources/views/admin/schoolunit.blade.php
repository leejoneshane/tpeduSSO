@extends('layouts.dashboard')

@section('page_heading')
<h1 class="page-header">行政部門管理</h1>
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
				<a href="#addSchoolUnitModal" class="btn btn-success" data-toggle="modal" style="float: right;margin-top: 3px;">
					<i class="glyphicon glyphicon-plus-sign"></i><span style="margin-left: 4px;">新增</span>
				</a>
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
							<form role="form" method="POST" action="{{ route('school.updateUnit', [ 'dc' => $dc, 'ou' => $ou->ou ]) }}">
							@csrf
							<td>
								<input id="ou" type="text" class="form-control" name="ou" value="{{ $ou->ou ? $ou->ou : old('ou') }}">
							</td>
							<td>
								<input id="description" type="text" class="form-control" name="description" value="{{ $ou->description ? $ou->description : old('description') }}">
							</td>
							<td>
								<button type="submit" style="margin-bottom: 4px;" class="btn btn-primary">修改</button>
								<button type="button" style="margin-bottom: 4px;" class="btn btn-danger"
									onclick="if(confirm('確定要刪除『'+$(this).parent().parent().find(':text:last').val()+'』?')){$('#remove-form').attr('action','{{ route('school.removeUnit', [ 'dc' => $dc, 'ou' => $ou->ou ]) }}');
											 $('#remove-form').submit();}">刪除</button>
							</td>
							</form>
						</tr>
						@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
    
		<form id="remove-form" action="" method="POST" style="display: none;">
		@csrf
		</form>
	</div>

	<div id="addSchoolUnitModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="panel-default">
					<div class="panel-heading">
						<button type="button" data-dismiss="modal" aria-label="Close" class="close" style="padding: 8px 0;">
							<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
						</button>
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
</div>
@endsection