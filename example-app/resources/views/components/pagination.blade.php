{{-- Simple, large-target pagination used by Paginator::defaultView() --}}
@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-between gap-3" aria-label="Pagination">
        <p class="text-sm text-slate-600">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>

        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary btn-sm opacity-50">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm" rel="prev">Previous</a>
            @endif

            <span class="px-2 py-1.5 text-sm text-slate-600">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm" rel="next">Next</a>
            @else
                <span class="btn btn-secondary btn-sm opacity-50">Next</span>
            @endif
        </div>
    </nav>
@endif
