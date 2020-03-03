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
                    <div class="card-header"><h4>授權警告</h4></div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <p><strong>依照個資法規定通知您：</strong></p>
                                <p><strong>由於您未滿13歲，系統無法依照您的要求將您的個人資料提供給 {{ $client }}。</strong></p>
                                <p><strong>請您的家長先到本網站（ldap.tp.edu.tw）註冊家長帳號後，代為行使同意權，然後您才能繼續登入 {{ $client }}。</strong></p>
                                <p><strong>如果您的家長已經代為行使同意權，那麼您需要請家長提高 {{ $client }} 的信任等級。</strong></p>
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
