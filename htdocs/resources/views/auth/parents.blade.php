<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - 授權</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        .passport-authorize .container {
            margin-top: 30px;
        }

        .passport-authorize .scopes {
            margin-top: 20px;
        }

        .passport-authorize .buttons {
            margin-top: 25px;
            text-align: center;
        }

        .passport-authorize .btn {
            width: 125px;
        }

        .passport-authorize .btn-approve {
            margin-right: 15px;
        }

        .passport-authorize form {
            display: inline;
        }
    </style>
</head>
<body class="passport-authorize">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-default">
                    <div class="card-header">
                        <h4>授權警告</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <p><strong>依照教育目的合理使用原則通知您：</strong></p>
                                <p><strong>由於您並非教育人員（即教師或學生），因此系統無法依照您的要求將您的個人資料提供給教育服務網站。</strong></p>
                                <p><strong>註冊於本系統的家長帳號，僅用於代理您的子女行使同意權。</strong></p>
                                <p><strong>未來台北通將提供全市單一登入功能，屆時您的家長帳號將會被台北通帳號取代，造成您的不便敬請見諒！</strong></p>
                            </div>
                            <div class="buttons">
                                <button class="btn btn-danger" onclick="window.history.go(-2);">我知道了！</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
