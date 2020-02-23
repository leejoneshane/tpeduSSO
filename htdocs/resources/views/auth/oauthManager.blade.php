@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">金鑰管理</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    @if (Auth::user()->is_admin || Auth::user()->id == 1)
                    <passport-clients></passport-clients>
                    @endif
                    <passport-authorized-clients></passport-authorized-clients>
                    <passport-personal-access-tokens></passport-personal-access-tokens>
                </div>
            </div>
        </div>
	</div>
</div>
@endsection