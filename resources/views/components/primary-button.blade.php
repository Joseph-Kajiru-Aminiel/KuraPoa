@props([
    'type' => 'submit',
    'loadingLabel' => __('Inatuma...'),
])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-forest bg-forest px-5 py-3 text-sm font-bold text-white transition-colors duration-150 hover:bg-panel focus-visible:ring-forest disabled:cursor-wait disabled:opacity-70']) }}
    x-bind:disabled="typeof submitting !== 'undefined' && submitting"
    x-bind:aria-busy="typeof submitting !== 'undefined' && submitting ? 'true' : 'false'"
>
    <span class="inline-flex items-center gap-2" x-show="typeof submitting === 'undefined' || !submitting">
        {{ $slot }}
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M3 10a1 1 0 0 1 1-1h9.6L10.3 5.7a1 1 0 1 1 1.4-1.4l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 1 1-1.4-1.4l3.3-3.3H4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
        </svg>
    </span>
    <span x-cloak x-show="typeof submitting !== 'undefined' && submitting" class="inline-flex items-center gap-2">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-opacity=".35" stroke-width="3"/>
            <path d="M18 10a8 8 0 0 0-8-8" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
        {{ $loadingLabel }}
    </span>
</button>
