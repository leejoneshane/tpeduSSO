@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8" style="margin-left: 20%">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">我同意授權之專案</div>
                <div class="card-body">
                    @if (!$tokens)
                        <p class="mb-0">您尚未登入第三方應用服務。</p>
                    @else
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>專案名稱</th>
                                    <th>授權範圍</th>
                                    <th>使用期限</th>
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
                                        <span>{{ $token->expires_at }}</span>
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
            <form id="form" action="" method="POST" style="display: none;">
            @csrf
            </form>
        </div>
	</div>
</div>
@endsection