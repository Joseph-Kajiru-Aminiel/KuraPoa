@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex min-h-11 items-center border-b-2 border-primary px-2 text-sm font-semibold text-primary transition-colors duration-150 focus-visible:ring-primary'
            : 'inline-flex min-h-11 items-center border-b-2 border-transparent px-2 text-sm font-semibold text-muted transition-colors duration-150 hover:border-border hover:text-ink focus-visible:ring-primary';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
