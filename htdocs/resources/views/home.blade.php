@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">主控面板</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    您已經登入系統！<br>
                    @if (!$account_ready)
                    注意：您使用的預設帳號在您畢業（或離職）後會自動刪除，請務必修改為自訂帳號才能長久保留使用！<br>
                    　　　同時，G-Suite 帳號將在啟用自訂帳號後才能啟用！
                    @elseif ($create_gsuite)
                    要把您現在使用的帳號設定成您的 G-Suite 帳號嗎？<br>
                    設定完成後，將可以使用 {{$gsuite}} 收發電子郵件或使用其它 G-Suite 服務。<br>
                    <ul>
                        <li>郵箱網址為 <a href="https://mail.google.com/a/ms.tp.edu.tw">https://mail.google.com/a/ms.tp.edu.tw</a></li>
                        <li>雲端硬碟為 <a href="https://drive.google.com/a/ms.tp.edu.tw">https://drive.google.com/a/ms.tp.edu.tw</a></li>
                    </ul>
                    @elseif ($gsuite_ready)
                    請使用 {{$gsuite}} 收發電子郵件或使用其它 G-Suite 服務。
                    <ul>
                        <li>郵箱網址為 <a href="https://mail.google.com/a/ms.tp.edu.tw">https://mail.google.com/a/ms.tp.edu.tw</a></li>
                        <li>雲端硬碟為 <a href="https://drive.google.com/a/ms.tp.edu.tw">https://drive.google.com/a/ms.tp.edu.tw</a></li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
