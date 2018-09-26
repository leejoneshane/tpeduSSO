@extends('layouts.superboard')

@section('page_heading')
{{ $group }}群組成員一覽表
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
			<h4>{{ $group }}群組成員一覽表</h4>
		</div>
		<div class="panel-body">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>{{ $members['attribute'] }}</th>
					</tr>
				</thead>
				<tbody>
					<?php unset($members['attribute']); ?>
					@foreach ($members as $member)
					<tr>
						<td style="vertical-align: inherit;">
							<span>{{ $member }}</span>
						</td>
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
