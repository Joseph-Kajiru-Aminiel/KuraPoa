@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block min-h-11 w-full rounded-xl bg-primary-light px-4 py-3 text-start text-sm font-semibold text-primary transition-colors duration-150 focus-visible:ring-primary'
            : 'block min-h-11 w-full rounded-xl px-4 py-3 text-start text-sm font-semibold text-body transition-colors duration-150 hover:bg-page focus-visible:ring-primary';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
