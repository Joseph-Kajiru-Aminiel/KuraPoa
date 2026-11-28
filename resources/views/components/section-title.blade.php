@props(['title', 'description' => null, 'eyebrow' => null])

<div {{ $attributes }}>
    @if ($eyebrow)
        <p class="mb-2 text-sm font-semibold text-primary">{{ $eyebrow }}</p>
    @endif
    <h2 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $title }}</h2>
    @if ($description)
        <p class="mt-2 max-w-2xl text-base leading-7 text-muted">{{ $description }}</p>
    @endif
</div>
