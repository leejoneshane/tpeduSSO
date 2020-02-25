@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">個人存取金鑰</div>
                <div class="card-body">
                    <p>
                        這是您的個人存取金鑰. 這個金鑰僅會在此時顯示一次，請好好保管金鑰，一但遺失將無法補發！
                        您可以提供此金鑰給您信任的網站或手機應用程式，以便開發商能代理您的身份進行資料操作！
                    </p>

                    <textarea class="form-control" rows="10">{{ $token }}</textarea>
                </div>
                <div class="card-footer">
                    <a class="btn btn-secondary" href="{{ route('oauth') }}">關閉</a>
                </div>
            </div>
        </div>
	</div>
</div>
@endsection