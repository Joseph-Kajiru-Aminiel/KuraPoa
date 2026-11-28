@props(['title', 'description'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-border bg-card px-6 py-10 text-center']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-light text-primary">
        @isset($icon)
            {{ $icon }}
        @else
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M8 7h8m-8 5h8m-8 5h4M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
        @endisset
    </div>
    <h3 class="mt-4 text-base font-semibold text-ink">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">{{ $description }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
