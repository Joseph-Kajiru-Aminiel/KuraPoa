@props(['label', 'value', 'description' => null])

<x-card {{ $attributes }}>
    <dl>
        <dt class="text-sm font-medium text-muted">{{ $label }}</dt>
        <dd class="mt-2 text-3xl font-bold tracking-tight text-ink">{{ $value }}</dd>
    </dl>
    @if ($description)
        <p class="mt-2 text-sm text-muted">{{ $description }}</p>
    @endif
</x-card>
