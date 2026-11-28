@props([
    'candidate',
    'size' => 'md',
    'fullPhoto' => false,
])

@php
    $sizes = [
        'sm' => 'h-10 w-10 text-sm',
        'md' => 'h-12 w-12 text-base',
        'lg' => 'h-16 w-16 text-lg',
        'xl' => 'h-32 w-32 text-3xl',
    ];
    $photo = $fullPhoto
        ? ($candidate->photo ?: $candidate->photo_thumbnail)
        : ($candidate->photo_thumbnail ?: $candidate->photo);
    $nameParts = preg_split('/\s+/', trim((string) $candidate->name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = collect($nameParts ?: [])->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode('');
@endphp

@if ($photo)
    <img
        src="{{ asset('storage/'.$photo) }}"
        alt="{{ __('Picha ya :name', ['name' => $candidate->name]) }}"
        loading="lazy"
        {{ $attributes->merge(['class' => 'shrink-0 rounded-full border border-border object-cover '.($sizes[$size] ?? $sizes['md'])]) }}
    >
@else
    <span
        aria-hidden="true"
        {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-primary text-center font-bold text-white '.($sizes[$size] ?? $sizes['md'])]) }}
    >{{ $initials !== '' ? mb_strtoupper($initials) : '?' }}</span>
@endif
