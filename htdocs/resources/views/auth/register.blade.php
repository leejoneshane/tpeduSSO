@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header"><h4>註冊家長帳號</h4></div>

                <div class="card-body">
                    <p style="color:red">注意事項：
                        <ul>
                            <li>如果您已經有教師帳號，請直接登入後綁定親子連結，請勿再次註冊家長帳號。每個身分證字號僅能註冊一個帳號。</li>
                            <li>本網站所提供的家長帳號因涉及學生個資授權，為避免引起法律及親權爭議，請務必提供您的真實資料。</li>
                            <li>若您提供之資料與學生學籍資料監護人之記載不同，本網站得依法不提供任何服務，包含您本人及未滿 13 歲之小孩，將無法使用本網站所介接的所有教育應用服務。</li>
                            <li>若有冒用家長身分或其他人之身分註冊帳號，請自負偽造文書罪刑事責任。</li>
                        </ul>
                    </p>
                    <hr>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">真實姓名</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="idno" class="col-md-4 col-form-label text-md-right">身分證字號</label>

                            <div class="col-md-6">
                                <input id="idno" type="text" class="form-control{{ $errors->has('idno') ? ' is-invalid' : '' }}" name="idno" value="{{ old('idno') }}" required autofocus>

                                @if ($errors->has('idno'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('idno') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">電子郵件（這是您登入用的帳號）</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="mobile" class="col-md-4 col-form-label text-md-right">手機號碼</label>

                            <div class="col-md-6">
                                <input id="mobile" type="mobile" class="form-control{{ $errors->has('mobile') ? ' is-invalid' : '' }}" name="mobile" value="{{ old('mobile') }}" required>

                                @if ($errors->has('mobile'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('mobile') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">密碼</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">再輸入一次密碼</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    註冊家長帳號
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
