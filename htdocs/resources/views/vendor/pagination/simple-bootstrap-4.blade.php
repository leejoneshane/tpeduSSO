@if ($paginator->hasPages())
    <ul class="pagination" role="navigation">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link">上一頁</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">上一頁</a>
            </li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">下一頁</a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link">下一頁</span>
            </li>
        @endif
    </ul>
@endif
