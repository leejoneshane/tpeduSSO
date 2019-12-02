@extends('layouts.admin')

@section('content')
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3 style="font-size: 22px;text-align: center;font-weight: bold;">臺北市教育人員<br/>單一身份驗證服務</h3>
            </div>
            <ul class="list-unstyled components">
				<li {{ (Request::is('bureau/admin') ? 'class=active' : '') }}>
                    <a href="{{ route('bureau.admin') }}"><i class="fa fa-user-md fa-fw"></i>設定局端管理員</a>
                </li>
                <li>
                    <a href="#submenu1" data-toggle="collapse" {{ (Request::is('bureau/organization','bureau/organization/*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
						<i class="fa fa-sitemap fa-fw"></i>教育機構管理
					</a>
					@if(Request::is('bureau/organization','bureau/organization/*'))
					<ul class="list-unstyled collapse in" id="submenu1" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu1">
					@endif
                        <li {{ (Request::is('bureau/organization') ? 'class=active' : '') }}><a class="func-item" href="{{ route('bureau.organization') }}">線上編輯</a></li>
                        <li {{ (Request::is('bureau/organization/json') ? 'class=active' : '') }}><a class="func-item" href="{{ route('bureau.jsonOrg') }}">匯入JSON</a></li>
                    </ul>
                </li>
                <li {{ (Request::is('bureau/group','bureau/group/*') ? 'class=active' : '') }}>
                    <a href="{{ route('bureau.group') }}"><i class="fa fa-users" aria-hidden="true"></i>動態群組管理</a>
                </li>
                <li>
                    <a href="#submenu2" data-toggle="collapse" {{ (Request::is('bureau/people','bureau/people/*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
						<i class="fa fa-user fa-fw"></i>人員管理
					</a>
					@if(Request::is('bureau/people','bureau/people/*'))
					<ul class="list-unstyled collapse in" id="submenu2" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu2">
					@endif
                        <li {{ (Request::is('bureau/people') ? 'class=active' : '') }}><a class="func-item" href="{{ route('bureau.people') }}">瀏覽及搜尋</a></li>
                        <li {{ (Request::is('bureau/people/new') ? 'class=active' : '') }}><a class="func-item" href="{{ route('bureau.createPeople') }}">新增人員</a></li>
                        <li {{ (Request::is('bureau/people/json') ? 'class=active' : '') }}><a class="func-item" href="{{ route('bureau.jsonPeople') }}">匯入JSON</a></li>
                    </ul>
                </li>
                <li {{ (Request::is('bureau/thirdapp') ? 'class=active' : '') }}>
                    <a href="{{ route('bureau.thirdapp') }}"><i class="glyphicon glyphicon-asterisk" style="margin: 0 2px;"></i>第三方應用管理</a>
                </li>
                <li {{ (Request::is('bureau/OauthScopeAccessLog') ? 'class=active' : '') }}>
                    <a href="{{ route('bureau.OauthScopeAccessLog') }}"><i class="fa fa-list-alt fa-fw"></i>使用者授權同意日誌查詢</a>
                </li>
                <li {{ (Request::is('bureau/usagerecord') ? 'class=active' : '') }}>
                    <a href="{{ route('bureau.usagerecord') }}"><i class="fa fa-list-alt fa-fw"></i>系統作業日誌查詢</a>
                </li>
                <li {{ (Request::is('bureau/parentspolicies') ? 'class=active' : '') }}>
                    <a href="{{ route('bureau.parentspolicies') }}"><i class="fa fa-receipt fa-fw"></i></i>家長服務條款聲明內容維護</a>
                </li>
            </ul>
        </nav>
@stop