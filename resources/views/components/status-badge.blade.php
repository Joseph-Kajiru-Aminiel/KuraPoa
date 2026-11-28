@props(['variant' => 'secure'])

@php
    $styles = [
        'secure' => 'border-field-border bg-card text-forest',
        'success' => 'border-primary/30 bg-primary-light text-forest',
        'warning' => 'border-warning bg-cream text-ink',
        'neutral' => 'border-divider bg-card-cream text-muted-ink',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-8 items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-bold leading-4 '.$styles[$variant]]) }}>
    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M5 8V6a5 5 0 0 1 10 0v2h.5A1.5 1.5 0 0 1 17 9.5v7a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 16.5v-7A1.5 1.5 0 0 1 4.5 8H5Zm2 0h6V6a3 3 0 1 0-6 0v2Z" clip-rule="evenodd"/>
    </svg>
    {{ $slot }}
</span>
