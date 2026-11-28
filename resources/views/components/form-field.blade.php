@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'icon' => null,
    'required' => false,
    'hint' => null,
    'toggleable' => true,
])

@php
    $inputId = $attributes->get('id', $name);
    $hintId = $inputId.'-hint';
    $errorId = $inputId.'-error';
    $errorMessage = $errors->first($name);
    $inputValue = in_array($type, ['password', 'file'], true) ? null : old($name, $value);
    $describedBy = implode(' ', array_filter([$errorMessage ? $errorId : null, $hint ? $hintId : null]));
@endphp

<div {{ $attributes->only('class') }}>
    <label for="{{ $inputId }}" class="mb-2 block text-sm font-bold text-ink">
        {{ $label }}
        @if ($required)
            <span aria-hidden="true" class="text-danger">*</span>
        @endif
    </label>

    <div @if ($type === 'password' && $toggleable) x-data="{ visible: false }" @endif class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-muted-ink" aria-hidden="true">
                @if ($icon === 'email')
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M2.5 5A2.5 2.5 0 0 1 5 2.5h10A2.5 2.5 0 0 1 17.5 5v.7L10 10.3 2.5 5.7V5ZM2.5 7.5v7.5A2.5 2.5 0 0 0 5 17.5h10a2.5 2.5 0 0 0 2.5-2.5V7.5l-7 4.2a1 1 0 0 1-1 0l-7-4.2Z"/></svg>
                @elseif ($icon === 'registration')
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3.5A1.5 1.5 0 0 1 4.5 2h11A1.5 1.5 0 0 1 17 3.5v13a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 16.5v-13ZM6 5a1 1 0 1 0 0 2h8a1 1 0 1 0 0-2H6Zm0 4a1 1 0 1 0 0 2h5a1 1 0 1 0 0-2H6Zm0 4a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2H6Z" clip-rule="evenodd"/></svg>
                @else
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 8V6a5 5 0 0 1 10 0v2h.5A1.5 1.5 0 0 1 17 9.5v7a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 16.5v-7A1.5 1.5 0 0 1 4.5 8H5Zm2 0h6V6a3 3 0 1 0-6 0v2Z" clip-rule="evenodd"/></svg>
                @endif
            </span>
        @endif

        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ $inputValue }}"
            @if ($type === 'password' && $toggleable) x-bind:type="visible ? 'text' : 'password'" @endif
            @required($required)
            @if ($errorMessage) aria-invalid="true" @endif
            @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except(['class', 'id'])->merge([
                'class' => 'min-h-[46px] w-full rounded-xl border border-field-border bg-card-cream px-3.5 py-3 text-sm text-ink placeholder:text-muted-ink focus:border-forest focus:ring-forest '.($icon ? 'pl-10' : '').($type === 'password' && $toggleable ? ' pr-14' : ''),
            ]) }}
        >

        @if ($type === 'password' && $toggleable)
            <button type="button" class="absolute inset-y-0 right-1 flex min-h-11 min-w-11 items-center justify-center rounded-lg text-muted-ink hover:bg-cream focus-visible:ring-forest" @click="visible = !visible" :aria-label="visible ? @js(__('Ficha nenosiri')) : @js(__('Onyesha nenosiri'))" aria-controls="{{ $inputId }}">
                <svg x-show="!visible" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 3c-4.2 0-7.6 2.7-9 7 1.4 4.3 4.8 7 9 7s7.6-2.7 9-7c-1.4-4.3-4.8-7-9-7Zm0 11.5a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9Zm0-2a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                <svg x-cloak x-show="visible" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.3 2 2 3.3l3.1 3.1C3.3 7.5 1.9 9.1 1 10c1.4 4.3 4.8 7 9 7 1.5 0 2.9-.3 4.1-.9l2.6 2.6 1.3-1.3L3.3 2ZM10 15c-3 0-5.5-1.8-6.9-5 .6-.9 1.5-1.8 2.6-2.5l2 2A2.5 2.5 0 0 0 11 13l1.5 1.5c-.8.3-1.6.5-2.5.5Zm0-10c3 0 5.5 1.8 6.9 5-.5.9-1.2 1.7-2.1 2.3l1.4 1.4c1.3-1 2.3-2.2 2.8-3.7-1.4-4.3-4.8-7-9-7-.7 0-1.4.1-2.1.2l1.6 1.6H10Z"/></svg>
            </button>
        @endif
    </div>

    @if ($errorMessage)
        <p id="{{ $errorId }}" class="mt-2 text-sm font-medium text-danger">{{ $errorMessage }}</p>
    @elseif ($hint)
        <p id="{{ $hintId }}" class="mt-2 text-sm leading-5 text-muted-ink">{{ $hint }}</p>
    @endif
</div>
