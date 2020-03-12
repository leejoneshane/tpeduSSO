@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header"><h4>驗證您的電子郵件地址</h4></div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            一個全新的電子郵件地址確認連結已經寄送到您的信箱。
                        </div>
                    @endif
                    <p>
                        注意：
                        <ul>
                            <li>為了確認您的郵件信箱設定正確，系統已經寄出郵件地址驗證信，請先開啟您的電子郵件信箱，檢查是否已經收到信件。</li>
                            <li>如果沒有收到主旨為「郵件地址驗證信」的信件，請從右側選單點選「修改個資」檢查電子郵件信箱是否正確。</li>
                            <li>如果電子郵件地址有錯誤，請立即在「修改個資」頁面的「電子郵件」欄位中輸入正確的電子郵件地址，修改完畢後，再繼續下一步驟。</li>
                            <li>如果您已經確認目前輸入的電子郵件地址是正確的，<a href="{{ route('verification.resend') }}">請點擊這裡為您「重新傳送」！</a></li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
