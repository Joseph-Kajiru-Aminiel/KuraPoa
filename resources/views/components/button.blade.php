@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'border border-primary bg-primary text-white hover:border-primary-hover hover:bg-primary-hover',
        'secondary' => 'border border-primary bg-card text-primary hover:bg-primary-light',
        'danger' => 'border border-danger bg-card text-danger hover:bg-page',
    ];
    $classes = 'inline-flex min-h-11 items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold transition-colors duration-150 focus-visible:ring-primary';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes.' '.$variants[$variant]]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes.' '.$variants[$variant]]) }}>
        {{ $slot }}
    </button>
@endif
