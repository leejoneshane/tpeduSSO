@extends('layouts.dashboard')

@section('page_heading')
全校授權金鑰
@endsection

@section('section')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6" style="margin-left: 25%">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">全校授權金鑰</div>
                <div class="card-body">
                    <p>
                        這是您的個人存取金鑰. 這個金鑰僅會在此時顯示一次，請好好保管金鑰，一但遺失將無法補發！
                        您可以提供此金鑰給您信任的網站或手機應用程式，以便開發商能代理您的身份對學校所有人員個資進行資料操作！
                    </p>

                    <textarea class="form-control" rows="10">{{ $token->accessToken }}</textarea>
                </div>
                <div class="card-footer text-md-center">
                    <a class="btn btn-secondary" href="{{ route('school.tokens') }}">關閉</a>
                </div>
            </div>
        </div>
	</div>
</div>
@endsection