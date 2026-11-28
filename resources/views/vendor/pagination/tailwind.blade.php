@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Urambazaji wa kurasa" class="flex items-center justify-between gap-3">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-11 items-center rounded-xl border border-border px-4 text-sm font-semibold text-muted" aria-disabled="true">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-h-11 items-center rounded-xl border border-border bg-card px-4 text-sm font-semibold text-body hover:bg-page">{{ __('pagination.previous') }}</a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex min-h-11 items-center rounded-xl border border-border bg-card px-4 text-sm font-semibold text-body hover:bg-page">{{ __('pagination.next') }}</a>
            @else
                <span class="inline-flex min-h-11 items-center rounded-xl border border-border px-4 text-sm font-semibold text-muted" aria-disabled="true">{{ __('pagination.next') }}</span>
            @endif
        </div>

        <div class="hidden flex-1 items-center justify-between sm:flex">
            <p class="text-sm text-muted">
                Inaonyesha <span class="font-semibold text-ink">{{ $paginator->firstItem() }}</span>
                hadi <span class="font-semibold text-ink">{{ $paginator->lastItem() }}</span>
                kati ya <span class="font-semibold text-ink">{{ $paginator->total() }}</span> matokeo
            </p>
            <div class="flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-11 min-w-11 items-center justify-center px-2 text-sm text-muted">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl bg-primary px-3 text-sm font-semibold text-white" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl px-3 text-sm font-semibold text-body hover:bg-primary-light hover:text-primary" aria-label="Nenda ukurasa wa {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
    </nav>
@endif
