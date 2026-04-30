@if ($paginator->hasPages())
<nav class="flex items-center justify-between py-2" aria-label="Pagination">
    <p class="text-sm text-gray-400">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ number_format($paginator->total()) }}
    </p>
    <div class="flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 select-none">← Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-teal-700 hover:bg-teal-50 rounded-lg transition-colors font-medium">← Prev</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 py-1.5 text-sm text-gray-400">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 text-sm bg-teal-800 text-white rounded-lg font-semibold" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-teal-700 hover:bg-teal-50 rounded-lg transition-colors">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-teal-700 hover:bg-teal-50 rounded-lg transition-colors font-medium">Next →</a>
        @else
            <span class="px-3 py-1.5 text-sm text-gray-300 select-none">Next →</span>
        @endif
    </div>
</nav>
@endif
