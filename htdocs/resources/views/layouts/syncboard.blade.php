@extends('layouts.app')

@section('content')
		<div id="wrapper">
           <nav class="navbar-light sidebar" style="margin-top: 0px" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>全誼校務行政系統介接</a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('sync/ps/runtime_test') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.runtime_test') }}">連線測試</a>
                                </li>
                                <li {{ (Request::is('sync/ps/sync_school') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.sync_school') }}">同步學校</a>
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
                                <li {{ (Request::is('sync/ps/auto') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.auto') }}">自動同步</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>巨耀校務行政系統介接</a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('sync/js/runtime_test') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.runtime_test') }}">連線測試</a>
                                </li>
                                <li {{ (Request::is('sync/js/sync_school') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.sync_school') }}">同步學校</a>
                                </li>
                                <li {{ (Request::is('sync/js/sync_ou') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.sync_ou') }}">同步部門職稱</a>
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
                                <li {{ (Request::is('sync/js/auto') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.js.auto') }}">自動同步</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>虹橋校務行政系統介接</a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('sync/hs/runtime_test') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.runtime_test') }}">連線測試</a>
                                </li>
                                <li {{ (Request::is('sync/hs/sync_school') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.sync_school') }}">同步學校</a>
                                </li>
                                <li {{ (Request::is('sync/hs/sync_ou') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.sync_ou') }}">同步部門職稱</a>
                                </li>
                                <li {{ (Request::is('sync/hs/sync_class') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.sync_class') }}">同步班級</a>
                                </li>
                                <li {{ (Request::is('sync/hs/sync_subject') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.sync_subject') }}">同步教學科目</a>
                                </li>
                                <li {{ (Request::is('sync/hs/sync_teacher') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.sync_teacher') }}">同步教師</a>
                                </li>
                                <li {{ (Request::is('sync/hs/sync_student') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.sync_student') }}">同步學生</a>
                                </li>
                                <li {{ (Request::is('sync/hs/auto') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.hs.auto') }}">自動同步</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>資料維護</a>
                            <ul class="nav nav-second-level">
                                <li {{ (Request::is('sync/fix/remove_description') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.remove_description') }}">移除人員紀錄中的描述欄位</a>
                                </li>
                                <li {{ (Request::is('sync/fix/remove_fake') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.remove_fake') }}">移除假身份人員</a>
                                </li>
                                <li {{ (Request::is('sync/fix/remove_deleted') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.remove_deleted') }}">移除標記為已刪除人員</a>
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

