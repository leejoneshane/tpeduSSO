@extends('layouts.dashboard')

@section('page_heading')
建立全校授權金鑰
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
    <div class="col-sm-4 offset-sm-4">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">建立全校授權金鑰</div>
                <div class="card-body">
                    <form id="form" role="form" action="{{ route('school.createToken', [ 'dc' => $dc ]) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="col-form-label text-md-right">用途說明</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label text-md-right">授權範圍</label>
                            <div class="offset-md-2 col-md-8">
                                @foreach ($scopes as $scope)
                                    <div class="checkbox">
                                        <label>
                                            @if (in_array($scope->id, ['school', 'schoolAdmin']))
                                            <input type="hidden" name="scopes[]" value="{{ $scope->id }}">
                                            <input type="checkbox" checked disabled>
                                                {{ $scope->id }}（必要）
                                            @elseif ($scope->id == 'admin')
                                            <input type="checkbox" name="scopes[]" value="{{ $scope->id }}" disabled>
                                                {{ $scope->id }}（無法使用）
                                            @elseif ($scope->id == 'profile')
                                            <input type="checkbox" name="scopes[]" value="{{ $scope->id }}" checked>
                                                {{ $scope->id }}（建議）
                                            @else
                                            <input type="checkbox" name="scopes[]" value="{{ $scope->id }}">
                                                {{ $scope->id }}
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-10 text-md-center">
                            <button type="submit" class="btn btn-primary">儲存</button>
                        </div>
                    </form>
                </div>
            </div>
	</div>
</div>
@endsection