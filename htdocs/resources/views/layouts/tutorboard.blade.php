@extends('layouts.app')

@section('content')
		<div id="wrapper">
           <nav class="navbar-light sidebar" style="margin-top: 0px" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li class="py-2">
                            <a href="{{ route('tutor.student', [ 'dc' => $dc, 'ou' => $ou ]) }}"><i class="fa fa-child fa-fw"></i> 學生帳號管理</a>
                        </li>
                        <li class="py-2">
                            <a href="{{ route('tutor.link', [ 'dc' => $dc, 'ou' => $ou ]) }}"><i class="fa fa-user-check fa-fw"></i> 審核親子連結</a>
                        </li>
                        <li class="py-2">
                            <a href="{{ route('tutor.qrcode', [ 'dc' => $dc, 'ou' => $ou ]) }}"><i class="fa fa-qrcode fa-fw"></i>  學生QRCODE</a>
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

