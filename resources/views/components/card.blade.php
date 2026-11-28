@props(['padding' => 'default'])

@php
    $paddingClasses = [
        'default' => 'p-6',
        'compact' => 'p-4',
        'none' => 'p-0',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-border bg-card '.$paddingClasses[$padding].' shadow-soft transition-shadow duration-200 hover:shadow-soft-hover']) }}>
    {{ $slot }}
</div>
