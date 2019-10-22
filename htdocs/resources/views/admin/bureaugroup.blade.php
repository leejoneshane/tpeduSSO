@extends('layouts.superboard')

@section('page_heading')
<h1 class="page-header">動態群組管理</h1>
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

		<div class="panel-default">
			<div class="panel-heading">
				<a href="#addGroupModal" class="btn btn-success" data-toggle="modal" style="float: right;margin-top: 3px;">
					<i class="glyphicon glyphicon-plus-sign"></i><span style="margin-left: 4px;">新增</span>
				</a>
				<h4>動態群組一覽表</h4>
			</div>
			<div class="panel-body">
				<table class="table table-hover">
					<thead>
						<tr>
							<th style="min-width: 55px;">群組代號</th>
							<th>過濾條件</th>
							<th width="30%">管理</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($groups as $group)
						<tr>
							<form role="form" id="form" method="POST" action="{{ route('bureau.updateGroup', [ 'cn' => $group->cn ]) }}">
							@csrf
							<td style="vertical-align: inherit;">
								<input id="cn" type="text" class="form-control" name="cn" value="{{ $group->cn ? $group->cn : old('cn') }}">
							</td>
							<td style="vertical-align: inherit;">
								<span style="word-break: break-all;">{{ $group->url }}</span>
							</td>
							<td style="vertical-align: inherit;width: 200px">
								<button type="button" style="margin-bottom: 4px;" class="btn btn-success" onclick="$('#deleteform').attr('action','{{ route('bureau.showMember', [ 'cn' => $group->cn ]) }}');$('#deleteform').submit();">成員</button>
								<button type="button" style="margin-bottom: 4px;" class="btn btn-primary" onclick="$('#form').submit();">改名</button>
								<button type="button" style="margin-bottom: 4px;" class="btn btn-danger" onclick="if(confirm('確定要刪除群組『'+$(this).parent().parent().find(':text').val()+'』?')){$('#deleteform').attr('action','{{ route('bureau.removeGroup', [ 'cn' => $group->cn ]) }}');$('#deleteform').submit();}">刪除</button>
							</td>
							</form>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		<form id="deleteform" action="" method="POST" style="display: none;">
		@csrf
		</form>
	</div>

	<div id="addGroupModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">

				<div class="panel panel-default">
					<div class="panel-heading">
						<h4>新增動態群組</h4>
					</div>
					<div class="panel-body">
						<form role="form" method="POST" action="{{ route('bureau.createGroup') }}">
							@csrf
							<div class="form-group{{ $errors->has('new-grp') ? ' has-error' : '' }}">
								<label>群組代號</label>
								<input id="new-grp" type="text" class="form-control" name="new-grp" value="{{ $errors->has('new-grp') ? old('new-grp') : '' }}"  placeholder="請輸入英文字母及數字" required>
								@if ($errors->has('new-grp'))
									<p class="help-block">
										<strong>{{ $errors->first('new-grp') }}</strong>
									</p>
								@endif
							</div>
							<div class="form-group{{ $errors->has('model') ? ' has-error' : '' }}">
								<label>群組模型</label>
								<select id="model" class="form-control" name="model">
								@foreach ($model as $m => $mdesc)
									<option value="{{ $m }}">{{ $mdesc }}</option>
								@endforeach
								</select>
							</div>
							<div class="form-group{{ $errors->has('field') ? ' has-error' : '' }}">
								<label style="display: block;">過濾條件</label>
								(<select style="height: 34px;" id="field" name="field">
								@foreach ($fields as $f => $fdesc)
									<option value="{{ $f }}">{{ $fdesc }}</option>
								@endforeach
								</select>=<input style="height: 34px;" id="perform" type="text" name="perform" value="{{ $errors->has('perform') ? old('perform') : '' }}">)
							</div>
							<div class="form-group{{ $errors->has('url') ? ' has-error' : '' }}">
								<label>自訂過濾條件</label>
								<input class="form-control" id="url" type="text" name="url" value="{{ $errors->has('url') ? old('url') : '' }}" placeholder="範例 ldap:///ou=people,dc=tp,dc=edu,dc=tw?mail?sub?(displayName=李*)">
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