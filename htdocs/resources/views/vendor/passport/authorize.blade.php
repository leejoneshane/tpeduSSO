<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - 授權</title>

    <!-- Styles -->
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">

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
                        授權請求
                    </div>
                    <div class="card-body">
                        <!-- Introduction -->
                        <p><strong>{{ $client->name }}</strong> 請求您授權以便存取您的個人資訊。</p>

                        <!-- Scope List -->
                        @if (count($scopes) > 0)
                            <div class="scopes">
                                    <p><strong>授權內容如下：</strong></p>

                                    <ul>
                                        @foreach ($scopes as $scope)
                                            <li>{{ $scope->description }}</li>
                                        @endforeach
                                    </ul>
                            </div>
                        @endif
                        @if ($denyYearsAgo12)
                        <div class="scopes">
                        <p><strong>授權警告：</strong></p>
                            <p><strong>本應用程式未滿12歲須由家長進行學童個資授權同意 </strong></p>
                            <p><strong>您目前尚未滿12歲，請您的家長先登入本系統後，進行12歲以下學童個資授權同意，再重新使用本應用程式。</strong></p>
                        </div>
                        <div class="buttons">
                        <form method="post" action="{{ url('/oauth/authorize') }}">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                    
                                    <button class="btn btn-danger">結束授權</button>
                                    <input type="hidden" name="state" value="{{ $request->state }}">
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                            </form>                                    
                        </div>
                        @else
                        <div class="buttons">
                            <!-- Authorize Button -->
                            <form method="post" action="{{ url('/oauth/authorize') }}">
                                {{ csrf_field() }}

                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                                <button type="submit" class="btn btn-success btn-approve">同意</button>
                            </form>

                            <!-- Cancel Button -->
                            <form method="post" action="{{ url('/oauth/authorize') }}">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}

                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                                <button class="btn btn-danger">不同意</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
