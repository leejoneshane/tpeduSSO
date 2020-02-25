@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">已授權之專案</div>
                <div class="card-body">
                    @if (!$tokens)
                        <p class="mb-0">您尚未登入第三方應用服務。</p>
                    @else
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>專案名稱</th>
                                    <th>授權範圍</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tokens as $token)
                                <tr>
                                    <td style="vertical-align: middle;">{{ $token->client->name }}</td>
                                    <td style="vertical-align: middle;">
                                        <span>{{ $token->scopes ? implode(', ',$token->scopes) : '' }}</span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <button type="button" class="btn btn-danger"
                                            onclick="$('#form').attr('action','{{ route('revokeToken', [ 'token_id' => $token->id ]) }}');
                                                    $('#form').submit();">取消授權</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            @if ($is_schoolAdmin)
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>個人存取金鑰</span>
                        <a class="action-link" tabindex="-1" href="{{ route('newToken') }}">建立金鑰</a>
                    </div>
                </div>
                <div class="card-body">
                @if (!$personal)
                    <p class="mb-0">您尚未建立任何個人存取金鑰。</p>
                @else
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>識別名稱</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personal as $token)
                            <tr>
                                <td style="vertical-align: middle;">{{ $token->name }}</td>
                                <td style="vertical-align: middle;">
                                    <button type="button" class="btn btn-danger"
                                        onclick="$('#form').attr('action','{{ route('revokeToken', [ 'token_id' => $token->id ]) }}');
                                            $('#form').submit();">刪除金鑰</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                </div>
            </div>
            @endif
            <form id="form" action="" method="POST" style="display: none;">
            @csrf
            </form>
        </div>
	</div>
</div>
@endsection