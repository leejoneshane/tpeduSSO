@extends('layouts.admin')

@section('content')
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3 style="font-size: 22px;text-align: center;font-weight: bold;">臺北市教育人員<br/>單一身份驗證服務</h3>
            </div>
            <ul class="list-unstyled components">
				<li {{ (Request::is('school/*/admin') ? 'class=active' : '') }}>
                    <a class="func-item" href="{{ route('school.admin', [ 'dc' => $dc ]) }}"><i class="fa fa-user-md fa-fw"></i>設定管理員</a>
                </li>
                <li {{ (Request::is('school/*/profile') ? 'class=active' : '') }}>
                    <a class="func-item" href="{{ route('school.profile', [ 'dc' => $dc ]) }}"><i class="fa fa-list-alt fa-fw"></i>學校基本資料</a>
                </li>
                <li {{ (Request::is('school/*/unit') ? 'class=active' : '') }}>
                    <a class="func-item" href="{{ route('school.unit', [ 'dc' => $dc ]) }}"><i class="fa fa-sitemap fa-fw"></i>行政部門管理</a>
                </li>
                <li {{ (Request::is('school/*/role') ? 'class=active' : '') }}>
                    <a class="func-item" href="{{ route('school.unitrole', [ 'dc' => $dc ]) }}"><i class="fa fa-suitcase fa-fw"></i>職稱管理</a>
                </li>
                <li {{ (Request::is('school/class') ? 'class=active' : '') }}>
                    <a href="#submenu1" data-toggle="collapse" {{ (Request::is('school/*/*class*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="fa fa-graduation-cap fa-fw"></i> 班級管理
                    </a>
					@if(Request::is('school/*/*class*'))
					<ul class="list-unstyled collapse in" id="submenu1" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu1">
					@endif
						@if (!empty($sims))
						<li {{ (Request::is('school/*/sync_class') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.sync_class', [ 'dc' => $dc ]) }}">同步班級</a></li>
						@endif
                        <li {{ (Request::is('school/*/class') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.class', [ 'dc' => $dc ]) }}">編輯班級資訊</a></li>
                        <li {{ (Request::is('school/*/class/assign') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.assignClass', [ 'dc' => $dc ]) }}">管理班級配課</a></li>
                    </ul>
                </li>
                <li {{ (Request::is('school/subject') ? 'class=active' : '') }}>
                    <a href="#submenu2" data-toggle="collapse" {{ (Request::is('school/*/*subject*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="fa fa-flask fa-fw"></i> 教學科目管理
                    </a>
					@if(Request::is('school/*/*subject*'))
					<ul class="list-unstyled collapse in" id="submenu2" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu2">
					@endif
						@if (!empty($sims))
						<li {{ (Request::is('school/*/sync_subject') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.sync_subject', [ 'dc' => $dc ]) }}">同步教學科目</a></li>
						@endif
                        <li {{ (Request::is('school/*/subject') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.subject', [ 'dc' => $dc ]) }}">編輯科目資訊</a></li>
                    </ul>
                </li>
                <li {{ (Request::is('school/teacher') ? 'class=active' : '') }}>
                    <a href="#submenu3" data-toggle="collapse" {{ (Request::is('school/*/*teacher*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="fa fa-female fa-fw"></i> 教師管理
                    </a>
					@if(Request::is('school/*/*teacher*'))
					<ul class="list-unstyled collapse in" id="submenu3" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu3">
					@endif
						@if (!empty($sims))
                        <li {{ (Request::is('school/*/sync_teacher') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.sync_teacher', [ 'dc' => $dc ]) }}">同步教師</a></li>
						@endif
                        <li {{ (Request::is('school/*/teacher') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.teacher', [ 'dc' => $dc ]) }}">瀏覽及搜尋</a></li>
						@if (empty($sims))
                        <li {{ (Request::is('school/*/teacher/new') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.createTeacher', [ 'dc' => $dc ]) }}">新增教師</a></li>
                        <li {{ (Request::is('school/*/teacher/json') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.jsonTeacher', [ 'dc' => $dc ]) }}">匯入JSON</a></li>
						@endif
                    </ul>
                </li>
                <li {{ (Request::is('school/student') ? 'class=active' : '') }}>
                    <a href="#submenu4" data-toggle="collapse" {{ (Request::is('school/*/*student*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="fa fa-child fa-fw"></i> 學生管理
                    </a>
					@if(Request::is('school/*/*student*'))
					<ul class="list-unstyled collapse in" id="submenu4" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu4">
					@endif
						@if (!empty($sims))
                        <li {{ (Request::is('school/*/sync_student') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.sync_student', [ 'dc' => $dc ]) }}">同步學生</a></li>
						@endif
                        <li {{ (Request::is('school/*/student') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.student', [ 'dc' => $dc ]) }}">瀏覽及搜尋</a></li>
						@if (empty($sims))
                        <li {{ (Request::is('school/*/student/new') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.createStudent', [ 'dc' => $dc ]) }}">新增學生</a></li>
                        <li {{ (Request::is('school/*/student/json') ? 'class=active' : '') }}><a class="func-item" href="{{ route('school.jsonStudent', [ 'dc' => $dc ]) }}">匯入JSON</a></li>
						@endif
                    </ul>
                </li>
            </ul>
        </nav>
@stop