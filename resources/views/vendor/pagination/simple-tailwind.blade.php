@if ($paginator->hasPages())
<nav class="flex justify-between items-center py-2" aria-label="Pagination">
    @if ($paginator->onFirstPage())
        <span class="px-3 py-1.5 text-sm text-gray-300 select-none">← Prev</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-teal-700 hover:bg-teal-50 rounded-lg transition-colors font-medium">← Prev</a>
    @endif

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-teal-700 hover:bg-teal-50 rounded-lg transition-colors font-medium">Next →</a>
    @else
        <span class="px-3 py-1.5 text-sm text-gray-300 select-none">Next →</span>
    @endif
</nav>
@endif
