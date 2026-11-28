@props(['variant' => 'neutral'])

@php
    $variants = [
        'neutral' => 'border-border bg-page text-body',
        'primary' => 'border-primary bg-primary text-white',
        'success' => 'border-primary bg-primary-light text-primary',
        'warning' => 'border-accent bg-accent text-ink',
        'dark' => 'border-ink bg-ink text-white',
        'danger' => 'border-danger bg-danger text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-full border px-3 py-1 text-xs font-semibold '.$variants[$variant]]) }}>
    {{ $slot }}
</span>
