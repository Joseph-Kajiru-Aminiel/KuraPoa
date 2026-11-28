@props(['variant' => 'default'])

@php
    $authVariant = $variant === 'auth';
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full border p-1 '.($authVariant ? 'border-field-border bg-card-cream' : 'border-border bg-card')]) }} aria-label="{{ __('Language') }}">
    @foreach (['sw' => 'SW', 'en' => 'EN'] as $locale => $label)
        <form method="POST" action="{{ route('language.update', $locale) }}">
            @csrf
            <input type="hidden" name="return_path" value="{{ request()->getRequestUri() }}">
            <button type="submit"
                @class([
                    'min-w-10 rounded-full px-3 text-xs font-bold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2',
                    'min-h-11 focus-visible:ring-forest' => $authVariant,
                    'min-h-9 focus-visible:ring-primary' => ! $authVariant,
                    'bg-forest text-white' => $authVariant && app()->getLocale() === $locale,
                    'bg-primary text-white' => ! $authVariant && app()->getLocale() === $locale,
                    'text-muted-ink hover:bg-cream' => $authVariant && app()->getLocale() !== $locale,
                    'text-body hover:bg-primary-light hover:text-primary' => ! $authVariant && app()->getLocale() !== $locale,
                ])
                @if (app()->getLocale() === $locale) aria-current="true" @endif>
                {{ $label }}
            </button>
        </form>
    @endforeach
</div>
