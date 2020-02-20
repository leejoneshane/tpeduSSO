@extends('layouts.parent')

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
                    @if (!$kids)
                    由於您尚未建立有效的親子連結（或是導師尚未進行親子關係的審核），因此無法代理貴子弟進行個資授權！<br>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
