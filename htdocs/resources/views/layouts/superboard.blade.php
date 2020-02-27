@extends('layouts.app')

@section('content')
		<div id="wrapper">
           <nav class="navbar-light sidebar" style="margin-top: 0px" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li {{ (Request::is('bureau/admin') ? 'class="active"' : '') }}>
                            <a href="{{ route('bureau.admin') }}"><i class="fa fa-user-md fa-fw"></i>設定局端管理員</a>
                        </li>
                        <li {{ (Request::is('bureau/organization') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>教育機構管理</a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('bureau/organization') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.organization') }}">線上編輯</a>
                                </li>
                                <li {{ (Request::is('bureau/organization/json') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.jsonOrg') }}">匯入JSON</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('bureau/group') ? 'class="active"' : '') }}>
                            <a href="{{ route('bureau.group') }}"><i class="fa fa-group fa-fw"></i>動態群組管理</a>
                        </li>
                        <li {{ (Request::is('bureau/people') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-user fa-fw"></i>人員管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('bureau/people') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.people') }}">瀏覽及搜尋</a>
                                </li>
                                <li {{ (Request::is('bureau/people/new') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.createPeople') }}">新增人員</a>
                                </li>
                                <li {{ (Request::is('bureau/people/json') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.jsonPeople') }}">匯入JSON</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('bureau/project') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-user fa-fw"></i>介接專案管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('bureau/project') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.project') }}">編輯審核</a>
                                </li>
                                <li {{ (Request::is('bureau/project/new') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.createProject') }}">新增專案</a>
                                </li>
                                <li {{ (Request::is('bureau/client') ? 'class="active"' : '') }}>
                                    <a href="{{ route('bureau.client') }}">OAuth 用戶端管理</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
           	</nav>


        	<div id="page-wrapper">
				<div class="row">
                	<div class="col-lg-12">
                    	<h1 class="page-header">@yield('page_heading')</h1>
                	</div>
           		</div>
				<div class="row">  
					@yield('section')
            	</div>
        	</div>
        </div>
@stop

