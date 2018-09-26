@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8" style="margin-top: 20px">
        	@if (Auth::user()->is_admin)
            <passport-clients></passport-clients>
            @endif
            <passport-authorized-clients></passport-authorized-clients>
            <passport-personal-access-tokens></passport-personal-access-tokens>
        </div>
	</div>
</div>
@endsection
