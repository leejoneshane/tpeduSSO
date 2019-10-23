@extends('layouts.admin')

@section('content')
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3 style="font-size: 22px;text-align: center;font-weight: bold;">臺北市教育人員<br/>單一身份驗證服務</h3>
            </div>
            <ul class="list-unstyled components">
                <li>
					<a href="#submenu0" data-toggle="collapse" {{ (Request::is('oauth','profile','changeAccount','changePassword','personal/gsuitepage','listConnectChildren','showConnectChildrenAuthForm') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="glyphicon glyphicon-user" style="margin: 0 3px;"></i>個人資料管理
                    </a>
					@if(Request::is('oauth','profile','changeAccount','changePassword','personal/gsuitepage'))
					<ul class="list-unstyled collapse in" id="submenu0" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu0">
					@endif
                        <li {{ (Request::is('oauth') ? 'class=active' : '') }}><a class="func-item" href="{{ route('oauth') }}">金鑰管理</a></li>
                        <li {{ (Request::is('profile') ? 'class=active' : '') }}><a class="func-item" href="{{ route('profile') }}">修改個資</a>
                        @if(Auth::user() == null OR (Auth::user() != null AND Auth::user()->onlyRole('家長') != true))
                        <li {{ (Request::is('changeAccount') ? 'class=active' : '') }} ><a class="func-item" href="{{ route('changeAccount') }}">變更帳號</a></li>
                        <li {{ (Request::is('changePassword') ? 'class=active' : '') }}><a class="func-item" href="{{ route('changePassword') }}">變更密碼</a></li>
                        @endif
						@if(Auth::user() != null AND (Auth::user()->inRole('學生') OR Auth::user()->inRole('教師')))
						<li {{ (Request::is('personal/gsuitepage') ? 'class=active' : '') }} ><a class="func-item" href="{{ route('personal.gsuitepage') }}">使用G-Suite服務</a></li>
						@endif
                    </ul>
                </li>
				@if(Auth::user() != null AND Auth::user()->inRole('教師')==true)
                <li>
					<a href="#submenu1" data-toggle="collapse" {{ (Request::is('personal/tutor_student','personal/teacher_lessons') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="glyphicon glyphicon-user" style="margin: 0 3px;"></i>教師服務
                    </a>
					@if(Request::is('personal/tutor_student','personal/teacher_lessons'))
					<ul class="list-unstyled collapse in" id="submenu1" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu1">
					@endif
						<li {{ (Request::is('personal/tutor_student') ? 'class=active' : '') }}><a class="func-item" href="{{ route('personal.tutor_student') }}">導師班學生管理</a></li>
						<li {{ (Request::is('personal/teacher_lessons') ? 'class=active' : '') }}><a class="func-item" href="{{ route('personal.teacher_lessons') }}">G-Suite Class Room 建立</a></li>
                    </ul>
                </li>
				@endif
				@if(Auth::user() != null AND (Auth::user()->inRole('家長') OR Auth::user()->inRole('教師'))) 
                <li>
					<a href="#submenu2" data-toggle="collapse" {{ (Request::is('parents/listConnectChildren','parents/showConnectChildrenAuthForm') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="glyphicon glyphicon-user" style="margin: 0 3px;"></i>家長服務
                    </a>
					@if(Request::is('parents/listConnectChildren','parents/showConnectChildrenAuthForm'))
					<ul class="list-unstyled collapse in" id="submenu2" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu2">
					@endif
						<li {{ (Request::is('parents/listConnectChildren') ? 'class=active' : '') }} ><a class="func-item" href="{{ route('parents.listConnectChildren') }}">親子連結</a></li>
						<li {{ (Request::is('parents/showConnectChildrenAuthForm') ? 'class=active' : '') }}><a class="func-item" href="{{ route('parents.showConnectChildrenAuthForm') }}">12歲以下學童個資授權同意</a></li>
                    </ul>
                </li>
				@endif
            </ul>
        </nav>
@stop