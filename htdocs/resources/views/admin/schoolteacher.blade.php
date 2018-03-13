@extends('layouts.dashboard')

@section('page_heading')
教師管理
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
    	<div class="input-group custom-search-form">
			<select id="field" class="form-control" name="field">
			    <option value="uuid" {{ $my_field == 'uuid' ? 'selected' : '' }}>使用者唯一編號</option>
			    <option value="idno" {{ $my_field == 'idno' ? 'selected' : '' }}>身分證字號</option>
			    <option value="name" {{ $my_field == 'name' ? 'selected' : '' }}>姓名</option>
			    <option value="mail" {{ $my_field == 'mail' ? 'selected' : '' }}>電子郵件</option>
			    <option value="mobile" {{ $my_field == 'mobile' ? 'selected' : '' }}>手機號碼</option>
				@foreach ($ous as $ou => $desc)
			    	<option value="ou={{ $ou }}" {{ $my_field == "ou=".$ou ? 'selected' : '' }}>{{ $desc }}</option>
			    @endforeach
			    <option value="uid" {{ $my_field == 'uid' ? 'selected' : '' }}>登入名稱</option>
			</select>
        	<input type="text" class="form-control" id="keywords" name="keywords" placeholder="搜尋..." value="{{ old('keywords') }}">
            <span class="input-group-btn">
            	<button class="btn btn-default" type="button" onclick="location='{{ url()->current() }}?field=' + $('#field').val() + '&words=' + $('#keywords').val();">
            		<i class="fa fa-search"></i>
            	</button>
        	</span>
    	</div>
	</div>
	<div class="col-sm-12">
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4>
				教師一覽表
			</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>身分證字號</th>
						<th>姓名</th>
						<th>登入名稱</th>
						<th>職稱</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($teachers as $teacher)
					<tr>
						<td>
							<span class="form-control">{{ $teacher->idno }}</span>
						</td>
						<td>
							<span class="form-control">{{ $teacher->displayName }}</span>
						</td>
						<td>
							<span class="form-control">{{ join(',', $teacher->uid) }}</span>
						</td>
						<td>
							<span class="form-control">{{ $teacher->titleName }}</span>
						</td>
						<td>
							<a href="{{ route('school.updateteacher', [ 'uuid' => $teacher->uuid ]) }}?ou={{ $my_ou }}" class="btn btn-primary">修改</a>
							<a href="{{ route('school.removerole', [ 'role' => $role->cn ]) }}?ou={{ $my_ou }}" class="btn btn-danger">刪除</a>
						</td>
						</form>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		</div>
	</div>
	</div>
</div>
@endsection
