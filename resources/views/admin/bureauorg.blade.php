@extends('layouts.superboard')

@section('page_heading')
教育機構管理
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
	<div class="col-sm-8">
		<div class="panel panel-default">	  
		<div class="panel-heading">
			<h4>
				<select id="area" name="area" onchange="location=$(this).val();">
				@foreach ($areas as $area)
			    	<option value="{{ route('bureau.organization') }}?area={{ $area }}" {{ $my_area == $area ? 'selected' : '' }}>{{ $area }}</option>
			    @endforeach
				</select>
				教育機構一覽表　　
				<span style="vertical-align: inherit;">
					<button type="button" class="btn btn-success"
						onclick="$('#form').attr('action','{{ route('bureau.createOrg') }}');
								 $('#form').attr('method', 'GET');
								 $('#form').submit();">新增機構</button>
				</span>				
			</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>系統代號</th>
						<th>統一編號</th>
						<th>全銜</th>
						<th>管理</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($schools as $school)
					<tr>
		    			@csrf
						<td style="vertical-align: inherit;">
							<span>{{ $school->o }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $school->tpUniformNumbers }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $school->description }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<button type="button" class="btn btn-primary"
							 	onclick="$('#form').attr('action','{{ route('bureau.updateOrg', [ 'dc' => $school->o ]) }}');
										 $('#form').attr('method', 'GET');
										 $('#form').submit();">編輯</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('bureau.removeOrg', [ 'dc' => $school->o ]) }}');
										 $('#form').submit();">刪除</button>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		</div>
	</div>

    <form id="form" action="" method="POST" style="display: none;">
    @csrf
    </form>
	</div>
</div>
@endsection
