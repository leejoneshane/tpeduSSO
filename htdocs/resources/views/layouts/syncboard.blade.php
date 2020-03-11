@extends('layouts.app')

@section('content')
		<div id="wrapper">
           <nav class="navbar-light sidebar" style="margin-top: 0px" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li class="py-2">
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>全誼校務行政系統介接</a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{ route('sync.ps.runtime_test') }}">連線測試</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.ps.sync_school') }}">同步學校</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.ps.sync_class') }}">同步班級</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.ps.sync_subject') }}">同步教學科目</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.ps.sync_teacher') }}">同步教師</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.ps.sync_student') }}">同步學生</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.ps.auto') }}">自動同步</a>
                                </li>
                            </ul>
                        </li>
                        <li class="py-2">
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>巨耀校務行政系統介接</a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{ route('sync.js.runtime_test') }}">連線測試</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.js.sync_school') }}">同步學校</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.js.sync_ou') }}">同步部門職稱</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.js.sync_class') }}">同步班級</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.js.sync_subject') }}">同步教學科目</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.js.sync_teacher') }}">同步教師</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.js.sync_student') }}">同步學生</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.js.auto') }}">自動同步</a>
                                </li>
                            </ul>
                        </li>
                        <li class="py-2">
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>虹橋校務行政系統介接</a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{ route('sync.hs.runtime_test') }}">連線測試</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.hs.sync_school') }}">同步學校</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.hs.sync_ou') }}">同步部門職稱</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.hs.sync_class') }}">同步班級</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.hs.sync_subject') }}">同步教學科目</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.hs.sync_teacher') }}">同步教師</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.hs.sync_student') }}">同步學生</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.hs.auto') }}">自動同步</a>
                                </li>
                            </ul>
                        </li>
                        <li class="py-2">
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>資料維護</a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="{{ route('sync.transfer_domain') }}">Gsuite 域名轉移</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.remove_parent') }}">移除未連結家長帳號</a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.remove_fake') }}">移除假身份人員</a>
                                </li>
                                <li>
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

