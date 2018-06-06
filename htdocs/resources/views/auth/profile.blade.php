@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header">修改個資</div>

                <div class="card-body">
				@if (session('error'))
		    		<div class="alert alert-danger">
					{{ session('error') }}
		    		</div>
				@endif
				@if (session('success'))
		    		<div class="alert alert-success">
					{{ session('success') }}
		    		</div>
				@endif

            	<form method="POST" action="{{ route('profile') }}">
            	@csrf
            	    <div class="row">
            		<div class="col-md-4 text-md-right">身份別</div>
            		<div class="col-md-6 text-md-left">{{ $user->ldap['employeeType'] }}</div>
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">姓名</div>
            		<div class="col-md-6 text-md-left">{{ $user->name }}</div>
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">學校</div>
            		<div class="col-md-6">{{ $user->ldap['school'] }}</div>
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">性別</div>
            		@if ($user->ldap['gender'] == 0)
            		<div class="col-md-6">未填寫</div>
            		@endif
            		@if ($user->ldap['gender'] == 1)
            		<div class="col-md-6">男</div>
            		@endif
            		@if ($user->ldap['gender'] == 2)
            		<div class="col-md-6">女</div>
            		@endif
            		@if ($user->ldap['gender'] == 9)
            		<div class="col-md-6">其它</div>
            		@endif
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">出生日期</div>
            		<div class="col-md-6">{{ isset($user->ldap['birthDate']) ? $user->ldap['birthDate'] : '' }}</div>
                    </div>
            	    <div class="form-group row">
            		<label for="email" class="col-md-4 col-form-label text-md-right">電子郵件</label>
            		<div class="col-md-6">
            		    <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $user->email }}" required autofocus>
            		    
            		    @if ($errors->has('email'))
            			<span class="invalid-feedback">
            			    <strong>{{ $errors->first('email') }}</strong>
            			</span>
            		    @endif
            		</div>
                    </div>
            	    <div class="form-group row">
            		<div class="col-md-6 offset-md-4">
            		    <div class="checkbox">
            			<label>
            			    <input type="checkbox" name="login-by-email" {{ $user->ldap['email_login'] ? 'checked' : '' }}>允許使用電子郵件代替自訂帳號進行登入
            			</label>
            		    </div>
            		</div>
                    </div>
            	    <div class="form-group row">
            		<label for="mobile" class="col-md-4 col-form-label text-md-right">手機號碼</label>

            		<div class="col-md-6">
            		    <input id="mobile" type="text" class="form-control{{ $errors->has('mobile') ? ' is-invalid' : '' }}" name="mobile" value="{{ isset($user->ldap['mobile']) ? $user->ldap['mobile'] : '' }}">
            		    
            		    @if ($errors->has('mobile'))
            			<span class="invalid-feedback">
            			    <strong>{{ $errors->first('mobile') }}</strong>
            			</span>
            		    @endif
            		</div>
                    </div>
            	    <div class="form-group row">
            		<div class="col-md-6 offset-md-4">
            		    <div class="checkbox">
            			<label>
            			    <input type="checkbox" name="login-by-mobile" {{ $user->ldap['mobile_login'] ? 'checked' : '' }}>允許使用手機號碼代替自訂帳號進行登入
            			</label>
            		    </div>
            		</div>
                    </div>
                    @if ($user->ldap['employeeType'] == '教師')
                	@if (array_key_exists('department', $user->ldap))
            		<div class="row">
            		    <div class="col-md-4 text-md-right">單位</div>
            		    <div class="col-md-6">{{ $user->ldap['department'] }}</div>
                	</div>
                	@endif
                	@if (array_key_exists('titleName', $user->ldap))
            		<div class="row">
            		    <div class="col-md-4 text-md-right">職稱</div>
            		    <div class="col-md-6">{{ $user->ldap['titleName'] }}</div>
                	</div>
                	@endif
                	@if (array_key_exists('tpTeachClass',$user->ldap))
            		<div class="row">
            		    <div class="col-md-4 text-md-right">任教班級</div>
            		    @if (is_array($user->ldap['tpTeachClass']))
            		    @php ($class_list = implode(" ",$user->ldap['tpTeachClass']))
            		    @else
            		    @php ($class_list = $user->ldap['tpTeachClass'])
            		    @endif
    	        	    <div class="col-md-6">{{ $class_list }}</div>
	                </div>
                        @endif
                    @endif
                    @if ($user->ldap['employeeType'] == '學生')
            	    <div class="row">
            		<div class="col-md-4 text-md-right">就讀班級</div>
            		@if (array_key_exists('tpClassTitle', $user->ldap))
            		<div class="col-md-6">{{ $user->ldap['tpClassTitle'] }}</div>
            		@else
            		<div class="col-md-6">{{ $user->ldap['tpClass'] }}</div>
            		@endif
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">座號</div>
            		<div class="col-md-6">{{ $user->ldap['tpSeat'] }}</div>
                    </div>
                    @endif
                    <div class="form-group row mb-0">
                	<div class="col-md-8 offset-md-4">
                	    <button type="submit" class="btn btn-primary">
                		確定
                	    </button>
                	</div>
            	    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
