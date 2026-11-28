@props(['type' => 'info'])

@php
    $styles = [
        'success' => 'border-primary bg-primary-light text-primary',
        'error' => 'border-danger bg-white text-danger',
        'warning' => 'border-warning bg-white text-ink',
        'info' => 'border-border bg-white text-body',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border-l-4 px-4 py-3 text-sm leading-6 '.$styles[$type], 'role' => $type === 'error' ? 'alert' : 'status', 'aria-live' => 'polite']) }}>
    {{ $slot }}
</div>
