@if ($paginator->hasPages())
    <ul class="pagination" role="navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true"><span>上一頁</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">上一頁</a></li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">下一頁</a></li>
        @else
            <li class="disabled" aria-disabled="true"><span>下一頁</span></li>
        @endif
    </ul>
@endif
