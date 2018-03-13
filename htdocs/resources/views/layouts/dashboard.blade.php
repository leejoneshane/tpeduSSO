@extends('layouts.admin')

@section('content')
		<div id="wrapper">
           <nav class="navbar-light sidebar" style="margin-top: 0px" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li {{ (Request::is('school/admin') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.admin') }}"><i class="fa fa-user-md fa-fw"></i> 設定管理員</a>
                        </li>
                        <li {{ (Request::is('school/profile') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.profile') }}"><i class="fa fa-list-alt fa-fw"></i> 學校基本資料</a>
                        </li>
                        <li {{ (Request::is('school/unit') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.unit') }}"><i class="fa fa-sitemap fa-fw"></i> 行政部門管理</a>
                        </li>
                        <li {{ (Request::is('school/class') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.class') }}"><i class="fa fa-graduation-cap fa-fw"></i> 班級管理</a>
                        </li>
                        <li {{ (Request::is('school/role') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.role') }}"><i class="fa fa-suitcase fa-fw"></i> 職稱管理</a>
                        </li>
                        <li {{ (Request::is('school/teacher') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-female fa-fw"></i> 教師管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('school/teacher') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.teacher') }}">瀏覽及搜尋</a>
                                </li>
                                <li {{ (Request::is('school/teacher/new') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.createTeacher' ) }}">新增教師</a>
                                </li>
                                <li {{ (Request::is('school/teacher/json') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.jsonTeacher' ) }}">匯入JSON</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('*forms') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-child fa-fw"></i> 學生管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('*panels') ? 'class="active"' : '') }}>
                                    <a href="{{ url('panels') }}">瀏覽及搜尋</a>
                                </li>
                                <li {{ (Request::is('*buttons') ? 'class="active"' : '') }}>
                                    <a href="{{ url('buttons' ) }}">新增學生</a>
                                </li>
                                <li {{ (Request::is('*notifications') ? 'class="active"' : '') }}>
                                    <a href="{{ url('notifications') }}">匯入JSON</a>
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

