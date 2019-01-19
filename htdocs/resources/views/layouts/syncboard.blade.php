@extends('layouts.admin')

@section('content')
		<div id="wrapper">
           <nav class="navbar-light sidebar" style="margin-top: 0px" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li {{ (Request::is('sync/ps') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>國小學程介接</a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('sync/ps/runtime_test') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.runtime_test') }}">連線測試</a>
                                </li>
                                <li {{ (Request::is('sync/ps/sync_class') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.sync_class') }}">同步班級</a>
                                </li>
                                <li {{ (Request::is('sync/ps/sync_subject') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.sync_subject') }}">同步教學科目</a>
                                </li>
                                <li {{ (Request::is('sync/ps/sync_teacher') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.sync_teacher') }}">同步教師</a>
                                </li>
                                <li {{ (Request::is('sync/ps/sync_student') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.sync_student') }}">同步學生</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('sync/js') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>國中學程介接</a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('sync/js/runtime_test') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.runtime_test') }}">連線測試</a>
                                </li>
                                <li {{ (Request::is('sync/js/sync_class') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.sync_class') }}">同步班級</a>
                                </li>
                                <li {{ (Request::is('sync/js/sync_subject') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.sync_subject') }}">同步教學科目</a>
                                </li>
                                <li {{ (Request::is('sync/js/sync_teacher') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.sync_teacher') }}">同步教師</a>
                                </li>
                                <li {{ (Request::is('sync/js/sync_student') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.sync_student') }}">同步學生</a>
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

