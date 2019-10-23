@extends('layouts.userboard')

@section('section')
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
				<p>注意：電子郵件將作為傳送帳號鎖定通知、重設密碼...等系統訊息使用，請務必填寫！</p>
            	<form method="POST" action="{{ route('profile') }}">
            	@csrf
            	    <div class="row">
            		<div class="col-md-4 text-md-right">身份別</div>
            		<div class="col-md-6 text-md-left">{{ !empty($user->ldap['employeeType']) ? $user->ldap['employeeType'] : '' }}</div>
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">姓名</div>
            		<div class="col-md-6 text-md-left">{{ $user->name }}</div>
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">性別</div>
            		@if (empty($user->ldap['gender']) || $user->ldap['gender'] == 0)
            		<div class="col-md-6">未填寫</div>
            		@endif
            		@if (!empty($user->ldap['gender']) && $user->ldap['gender'] == 1)
            		<div class="col-md-6">男</div>
            		@endif
            		@if (!empty($user->ldap['gender']) && $user->ldap['gender'] == 2)
            		<div class="col-md-6">女</div>
            		@endif
            		@if (!empty($user->ldap['gender']) && $user->ldap['gender'] == 9)
            		<div class="col-md-6">其它</div>
            		@endif
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">出生日期</div>
            		<div class="col-md-6">{{ !empty($user->ldap['birthDate']) ? $user->ldap['birthDate'] : '' }}</div>
                    </div>
            	    <div class="form-group row">
            		<label for="email" class="col-md-4 col-form-label text-md-right">電子郵件</label>
            		<div class="col-md-6">
            		    <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $user->email }}" placeholder="請勿填寫他人的電子郵件，以免密碼外洩。" required autofocus>            		    
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
            			    <input type="checkbox" name="login-by-email" value="yes"{{ $user->ldap['email_login'] ? ' checked' : '' }}>允許使用電子郵件代替自訂帳號進行登入
            			</label>
            		    </div>
            		</div>
                    </div>
            	    <div class="form-group row">
            		<label for="mobile" class="col-md-4 col-form-label text-md-right">手機號碼</label>
            		<div class="col-md-6">
            		    <input id="mobile" type="text" class="form-control{{ $errors->has('mobile') ? ' is-invalid' : '' }}" name="mobile" value="{{ isset($user->ldap['mobile']) ? $user->ldap['mobile'] : '' }}" placeholder="若無手機可以免填，請勿填寫家長的手機號碼。">
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
            			    <input type="checkbox" name="login-by-mobile" value="yes"{{ $user->ldap['mobile_login'] ? ' checked' : '' }}>允許使用手機號碼代替自訂帳號進行登入
            			</label>
            		    </div>
            		</div>
                    </div>
                    @if (!empty($user->ldap['employeeType']) && $user->ldap['employeeType'] == '教師')
                		@if (isset($user->ldap['school']))
							@foreach ($user->ldap['school'] as $o => $sch)
            	    			<div class="row">
            					<div class="col-md-4 text-md-right">學校</div>
            					<div class="col-md-6">{{ $sch }}</div>
                    			</div>
                				@if (!empty($user->ldap['department'][$o]))
								@foreach ($user->ldap['department'][$o] as $ou)
            					<div class="row">
            		    		<div class="col-md-4 text-md-right">單位</div>
            		    		<div class="col-md-6">{{ $ou->name }}</div>
                				</div>
								@endforeach
                				@endif
                				@if (!empty($user->ldap['titleName'][$o]))
								@foreach ($user->ldap['titleName'][$o] as $role)
            					<div class="row">
            		    		<div class="col-md-4 text-md-right">職稱</div>
            		    		<div class="col-md-6">{{ $role->name }}</div>
                				</div>
								@endforeach
                				@endif
                				@if (!empty($user->ldap['teachClass'][$o]))
								@foreach ($user->ldap['teachClass'][$o] as $class)
            					<div class="row">
            		    		<div class="col-md-4 text-md-right">任教班級</div>
            		    		<div class="col-md-6">{{ $class->name }}</div>
                				</div>
								@endforeach
                				@endif
							@endforeach
                        @endif
                    @endif
                    @if (!empty($user->ldap['employeeType']) && $user->ldap['employeeType'] == '學生')
            	    	<div class="row">
            			<div class="col-md-4 text-md-right">就讀班級</div>
            			<div class="col-md-6">{{ !empty($user->ldap['tpClassTitle']) ? $user->ldap['tpClassTitle'] : $user->ldap['tpClass'] }}</div>
                    	</div>
            	    	<div class="row">
            			<div class="col-md-4 text-md-right">座號</div>
            			<div class="col-md-6">{{ $user->ldap['tpSeat'] }}</div>
                    	</div>
                    @endif
                    <div class="form-group row mb-0">
                	<div class="col-md-8 offset-md-4">
                	    <button type="submit" class="btn btn-primary">確定</button>
                	</div>
            	    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
