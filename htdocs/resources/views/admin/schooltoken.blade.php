@extends('layouts.dashboard')

@section('page_heading')
代理授權金鑰管理
@endsection

@section('section')
<div class="container">
	<div class="row">
	@if (session('error'))
	    <div class="col-sm-8 alert alert-danger">
		{{ session('error') }}
	    </div>
	@endif
	@if (session('success'))
	    <div class="col-sm-8 alert alert-success">
		{{ session('success') }}
	    </div>
	@endif
    <div class="row justify-content-center">
        <div class="col-md-8" style="margin-left: 20%">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4>代理授權金鑰管理</h4>
                    </div>
                </div>
                <div class="card-body">
                @if (!$personal)
                    <p class="mb-0">您尚未建立任何全校授權金鑰。</p>
                @else
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>識別名稱（用途說明）</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personal as $token)
                            <tr>
                                <td style="vertical-align: middle;">{{ $token->name }}</td>
                                <td style="vertical-align: middle;">
                                    <button type="button" class="btn btn-danger"
                                        onclick="$('#form').attr('action','{{ route('school.revokeToken', [ 'token_id' => $token->id ]) }}');
                                            $('#form').submit();">刪除金鑰</button>
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