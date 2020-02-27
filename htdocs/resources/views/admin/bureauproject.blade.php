@extends('layouts.superboard')

@section('page_heading')
介接專案管理
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
			<h4>介接專案一覽表</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>申請單位</th>
						<th>應用平台名稱</th>
						<th>應用平台網址</th>
						<th>聯絡窗口</th>
						<th>審查結果</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($projects as $project)
					<tr>
		    			@csrf
						<td style="vertical-align: inherit;">
							<span>{{ $project->organization ?: '-'}}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $project->applicationName }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $project->website ?: '-'}}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $project->connUnit.$project->connName.' '.$project->connEmail.' '.$project->connTel }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<span>{{ $project->audit ? '核准' : '待審' }}</span>
						</td>
						<td style="vertical-align: inherit;">
							<button type="button" class="btn btn-primary"
								 onclick="$('#form').attr('action','{{ route('bureau.updateProject', [ 'id' => $project->id ]) }}');
										 $('#form').attr('method', 'GET');
										 $('#form').submit();">編輯</button>
							<button type="button" class="btn btn-danger"
							 	onclick="$('#form').attr('action','{{ route('bureau.removeProject', [ 'id' => $project->id ]) }}');
										 $('#form').submit();">刪除</button>
							<button type="button" class="btn btn-warning"
								onclick="$('#form').attr('action','{{ route('bureau.denyProject', [ 'id' => $project->id ]) }}');
										 $('#form').attr('method', 'GET');
										 $('#form').submit();">審核</button>
							@if (! $project->audit))
							<button type="button" class="btn btn-success"
								onclick="$('#form').attr('action','{{ route('bureau.passProject', [ 'id' => $project->id ]) }}');
										 $('#form').submit();">核准</button>
							@endif
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
