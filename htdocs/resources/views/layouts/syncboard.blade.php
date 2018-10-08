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
                                <li {{ (Request::is('sync/ps/sync_seat') ? 'class="active"' : '') }}>
                                    <a href="{{ route('sync.ps.sync_seat') }}">同步就讀班級座號</a>
                                </li>
                            </ul>
                        </li>
                        <li {{ (Request::is('bureau/organization') ? 'class="active"' : '') }}>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i>國中學程介接</a>
                            <ul class="nav nav-second-level">
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

