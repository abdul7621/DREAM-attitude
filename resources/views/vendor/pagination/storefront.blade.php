@if ($paginator->hasPages())
<nav class="sf-pagination" role="navigation" aria-label="Pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="sf-page-link sf-page-disabled">
            <i class="bi bi-chevron-left"></i> Previous
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="sf-page-link" rel="prev">
            <i class="bi bi-chevron-left"></i> Previous
        </a>
    @endif

    {{-- Page Numbers --}}
    <div class="sf-page-numbers">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="sf-page-link sf-page-dots">&hellip;</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="sf-page-link sf-page-active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="sf-page-link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="sf-page-link" rel="next">
            Next <i class="bi bi-chevron-right"></i>
        </a>
    @else
        <span class="sf-page-link sf-page-disabled">
            Next <i class="bi bi-chevron-right"></i>
        </span>
    @endif
</nav>
@endif
