@props(['light' => false, 'compact' => false, 'href' => null])

<a href="{{ $href ?? route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg class="h-9 w-9 shrink-0" viewBox="0 0 40 40" fill="none" aria-hidden="true">
        <path d="M14 6.5A2.5 2.5 0 0 1 16.5 4h8A2.5 2.5 0 0 1 27 6.5V25H14V6.5Z" fill="#FFFFFF" stroke="{{ $light ? '#0F172A' : '#E2E8F0' }}" stroke-width="1.5"/>
        <path d="m17 14 2.4 2.4L24 11.5" stroke="#D9A404" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="6" y="18" width="28" height="17" rx="4" fill="{{ $light ? '#FFFFFF' : '#0B6E4F' }}"/>
        <path d="M6 22h28M13 18l3-3h8l3 3" stroke="{{ $light ? '#0F172A' : '#095A41' }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="text-xl font-extrabold tracking-tight {{ $light ? 'text-white' : 'text-ink' }}">
        KuraPoa<span class="{{ $light ? 'text-white' : 'text-primary' }}">.</span>
    </span>
    @if (! $compact)
        <span class="sr-only">{{ __('Kura yako, sauti yako.') }}</span>
    @endif
</a>
