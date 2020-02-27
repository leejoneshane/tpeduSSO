@extends('layouts.dashboard')

@section('page_heading')
建立代理授權金鑰
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
    <div class="row justify-content-center">
        <div class="col-md-6" style="margin-left: 25%">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">建立代理授權金鑰</div>
                <div class="card-body">
                    <form id="form" role="form" action="{{ route('school.createToken') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="col-md-4 col-form-label text-md-right">用途說明</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 col-form-label text-md-right">授權範圍</label>
                            <div class="col-md-6">
                                @foreach ($scopes as $scope)
                                    <div class="checkbox">
                                        <label>
                                            @if (in_array($scope->id, ['school', 'schoolAdmin']))
                                            <input type="checkbox" name="scopes[]" value="{{ $scope->id }}" checked readonly>
                                            @elseif ($scopd->id == 'admin')
                                            <input type="checkbox" name="scopes[]" value="{{ $scope->id }}" disable>
                                            @else
                                            <input type="checkbox" name="scopes[]" value="{{ $scope->id }}"{{ $scope->id == 'profile' ? ' checked' : '' }}>
                                            @endif
                                            {{ $scope->id }}
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
</div>
@endsection