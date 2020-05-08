@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default" style="margin-top: 20px">
                <div class="card-header"><h4>修改個資</h4></div>

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
				<p>注意：
					<ul>
						<li>電子郵件將作為傳送帳號鎖定通知、重設密碼...等系統訊息使用，請務必填寫！</li>
						<li>學生請勿填寫家長的電子郵件，以免家長帳號無法註冊！</li>
						<li>請勿填寫他人的電子郵件，以免帳號密碼遭到篡改。</li>
						<li>若您尚未有電子郵件，請先離開此頁面，不要按「確定」。</li>
					</ul>
				</p>
				<hr>
            	<form method="POST" action="{{ route('profile') }}">
            	@csrf
            	    <div class="row">
            		<div class="col-md-4 text-md-right">身分別</div>
            		<div class="col-md-6 text-md-left">{{ !empty($user->ldap['employeeType']) ? $user->ldap['employeeType'] : '家長' }}</div>
                    </div>
            	    <div class="row">
            		<div class="col-md-4 text-md-right">姓名</div>
            		<div class="col-md-6 text-md-left">{{ $user->name }}</div>
					</div>
					@if (!$user->is_parent)
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
					@endif
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
					@if (!$user->is_parent)
            	    <div class="form-group row">
            		<div class="col-md-8 text-md-right">
            		    <div class="checkbox">
            			<label>
            			    <input type="checkbox" name="login-by-email" value="yes"{{ $user->ldap['email_login'] ? ' checked' : '' }}>允許使用電子郵件代替自訂帳號進行登入
            			</label>
            		    </div>
            		</div>
					</div>
					@endif
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
					@if (!$user->is_parent)
            	    <div class="form-group row">
            		<div class="col-md-8 text-md-right">
            		    <div class="checkbox">
            			<label>
            			    <input type="checkbox" name="login-by-mobile" value="yes"{{ $user->ldap['mobile_login'] ? ' checked' : '' }}>允許使用手機號碼代替自訂帳號進行登入
            			</label>
            		    </div>
            		</div>
					</div>
					@endif
                    @if (!empty($user->ldap['employeeType']) && $user->ldap['employeeType'] != '學生')
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
            			<div class="col-md-6">{{ !empty($user->ldap['tpSeat']) ? $user->ldap['tpSeat'] : '未輸入' }}</div>
                    	</div>
                    @endif
                    <div class="form-group row mb-0">
                	<div class="col-md-8 text-md-center">
                	    <button type="submit" class="btn btn-primary">確定</button>
                	</div>
            	    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
