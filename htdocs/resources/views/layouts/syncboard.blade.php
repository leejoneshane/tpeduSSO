@extends('layouts.admin')

@section('content')
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3 style="font-size: 22px;text-align: center;font-weight: bold;">臺北市教育人員<br/>單一身份驗證服務</h3>
            </div>
            <ul class="list-unstyled components">
                <li>
                    <a href="#submenu1" data-toggle="collapse" {{ (Request::is('sync/ps/*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="fa fa-sitemap fa-fw"></i>全誼校務行政系統介接
                    </a>
					@if(Request::is('sync/ps/*'))
					<ul class="list-unstyled collapse in" id="submenu1" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu1">
					@endif
                        <li {{ (Request::is('sync/ps/runtime_test') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.ps.runtime_test') }}">連線測試</a></li>
                        <li {{ (Request::is('sync/ps/sync_school') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.ps.sync_school') }}">同步學校</a>
                        <li {{ (Request::is('sync/ps/sync_class') ? 'class=active' : '') }} ><a class="func-item" href="{{ route('sync.ps.sync_class') }}">同步班級</a></li>
                        <li {{ (Request::is('sync/ps/sync_subject') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.ps.sync_subject') }}">同步教學科目</a></li>
                        <li {{ (Request::is('sync/ps/sync_teacher') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.ps.sync_teacher') }}">同步教師</a></li>
                        <li {{ (Request::is('sync/ps/sync_student') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.ps.sync_student') }}">同步學生</a></li>
                        <li {{ (Request::is('sync/ps/auto') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.ps.auto') }}">自動同步</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#submenu2" data-toggle="collapse" {{ (Request::is('sync/js/*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="fa fa-sitemap fa-fw"></i>巨耀校務行政系統介接
                    </a>
					@if(Request::is('sync/js/*'))
					<ul class="list-unstyled collapse in" id="submenu2" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu2">
					@endif
                        <li {{ (Request::is('sync/js/runtime_test') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.js.runtime_test') }}">連線測試</a></li>
                        <li {{ (Request::is('sync/js/sync_school') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.js.sync_school') }}">同步學校</a></li>
                        <li {{ (Request::is('sync/js/sync_class') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.js.sync_class') }}">同步班級</a></li>
                        <li {{ (Request::is('sync/js/sync_subject') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.js.sync_subject') }}">同步教學科目</a></li>
                        <li {{ (Request::is('sync/js/sync_teacher') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.js.sync_teacher') }}">同步教師</a></li>
                        <li {{ (Request::is('sync/js/sync_student') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.js.sync_student') }}">同步學生</a></li>
                        <li {{ (Request::is('sync/js/auto') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.js.auto') }}">自動同步</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#submenu3" data-toggle="collapse" {{ (Request::is('sync/fix/*') ? 'aria-expanded=true' : 'aria-expanded=false') }} class="dropdown-toggle">
                        <i class="fa fa-sitemap fa-fw"></i>資料維護
                    </a>
					@if(Request::is('sync/fix/*'))
					<ul class="list-unstyled collapse in" id="submenu3" aria-expanded="true">
					@else
                    <ul class="list-unstyled collapse" id="submenu3">
					@endif
                        <li {{ (Request::is('sync/fix/remove_fake') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.remove_fake') }}">移除假身份人員</a></li>
                        <li {{ (Request::is('sync/fix/remove_deleted') ? 'class=active' : '') }}><a class="func-item" href="{{ route('sync.remove_deleted') }}">移除標記為已刪除人員</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
@stop