@extends('layouts.admin')

@section('content')
		<div id="wrapper">
           <nav class="navbar-light sidebar" style="margin-top: 0px" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li {{ (Request::is('school/admin') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.admin', [ 'dc' => $dc ]) }}"><i class="fa fa-user-md fa-fw"></i> 設定管理員</a>
                        </li>
                        <li {{ (Request::is('school/profile') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.profile', [ 'dc' => $dc ]) }}"><i class="fa fa-list-alt fa-fw"></i> 學校基本資料</a>
                        </li>
                        <li {{ (Request::is('school/unit') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.unit', [ 'dc' => $dc ]) }}"><i class="fa fa-sitemap fa-fw"></i> 行政部門管理</a>
                        </li>
                        <li {{ (Request::is('school/role') ? 'class="active"' : '') }}>
                            <a href="{{ route('school.role', [ 'dc' => $dc, 'ou' => 'null' ]) }}"><i class="fa fa-suitcase fa-fw"></i> 職稱管理</a>
                        </li>
                        <li {{ (Request::is('school/class') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-graduation-cap fa-fw"></i> 班級管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                @if ($category == '國民小學' || $category == '幼兒園')
                                <li {{ (Request::is('ps/sync_class') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.ps.sync_class', [ 'dc' => $dc ]) }}">同步班級</a>
                                </li>
                                @endif
                                @if ($category == '國民中學' || $category == '高中')
                                <li {{ (Request::is('js/sync_class') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.js.sync_class', [ 'dc' => $dc ]) }}">同步班級</a>
                                </li>
                                @endif
                                <li {{ (Request::is('school/class') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.class', [ 'dc' => $dc ]) }}">編輯班級資訊</a>
                                </li>
                                <li {{ (Request::is('school/class/assign') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.assignClass', [ 'dc' => $dc ]) }}">管理班級配課</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('school/subject') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-flask fa-fw"></i> 教學科目管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                @if ($category == '國民小學' || $category == '幼兒園')
                                <li {{ (Request::is('ps/sync_subject') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.ps.sync_subject', [ 'dc' => $dc ]) }}">同步教學科目</a>
                                </li>
                                @endif
                                @if ($category == '國民中學' || $category == '高中')
                                <li {{ (Request::is('js/sync_subject') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.js.sync_subject', [ 'dc' => $dc ]) }}">同步教學科目</a>
                                </li>
                                @endif
                                <li {{ (Request::is('school/subject') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.subject', [ 'dc' => $dc ]) }}">編輯科目資訊</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('school/teacher') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-female fa-fw"></i> 教師管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                @if ($category == '國民小學' || $category == '幼兒園')
                                <li {{ (Request::is('ps/sync_teacher') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.ps.sync_teacher', [ 'dc' => $dc ]) }}">同步教師</a>
                                </li>
                                @endif
                                @if ($category == '國民中學' || $category == '高中')
                                <li {{ (Request::is('js/sync_teacher') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.js.sync_teacher', [ 'dc' => $dc ]) }}">同步教師</a>
                                </li>
                                @endif
                                <li {{ (Request::is('school/teacher') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.teacher', [ 'dc' => $dc ]) }}">瀏覽及搜尋</a>
                                </li>
                                <li {{ (Request::is('school/teacher/new') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.createTeacher', [ 'dc' => $dc ]) }}">新增教師</a>
                                </li>
                                <li {{ (Request::is('school/teacher/json') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.jsonTeacher', [ 'dc' => $dc ]) }}">匯入JSON</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('school/student') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-child fa-fw"></i> 學生管理<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                @if ($category == '國民小學' || $category == '幼兒園')
                                <li {{ (Request::is('ps/sync_student') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.ps.sync_student', [ 'dc' => $dc ]) }}">同步學生</a>
                                </li>
                                @endif
                                @if ($category == '國民中學' || $category == '高中')
                                <li {{ (Request::is('js/sync_student') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.js.sync_student', [ 'dc' => $dc ]) }}">同步學生</a>
                                </li>
                                @endif
                                <li {{ (Request::is('school/student') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.student', [ 'dc' => $dc ]) }}">瀏覽及搜尋</a>
                                </li>
                                <li {{ (Request::is('school/student/new') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.createStudent', [ 'dc' => $dc ]) }}">新增學生</a>
                                </li>
                                <li {{ (Request::is('school/student/json') ? 'class="active"' : '') }}>
                                    <a href="{{ route('school.jsonStudent', [ 'dc' => $dc ]) }}">匯入JSON</a>
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

