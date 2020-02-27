@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6" style="margin-left: 25%">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">建立存取金鑰</div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            <p class="mb-0"><strong>糟糕！</strong> 發生錯誤！</p>
                            <br>
                                <ul>
                                @if (is_array(session('error')))
                                    @foreach (session('error') as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                @else
                                    <li>{{ session('error') }}</li>
                                @endif
                                </ul>
                        </div>
                    @endif
                    <form id="form" role="form" action="{{ route('storeToken') }}" method="POST">
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