@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
])

@php
    $inputId = $attributes->get('id', $name);
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $errorMessage = $errors->first($name);
    $inputValue = in_array($type, ['password', 'file'], true) ? null : old($name, $value);
@endphp

<div {{ $attributes->only('class') }}>
    <label for="{{ $inputId }}" class="mb-2 block text-sm font-semibold text-ink">
        {{ $label }}
        @if ($required)
            <span aria-hidden="true" class="text-danger">*</span>
        @endif
    </label>

    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $inputValue }}"
        @required($required)
        @if ($errorMessage) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
        {{ $attributes->except('class')->merge(['class' => 'min-h-11 w-full rounded-xl border border-border bg-card px-4 py-2.5 text-sm text-ink placeholder:text-muted focus:border-primary focus:ring-primary']) }}
    >

    @if ($errorMessage)
        <p id="{{ $inputId }}-error" class="mt-2 text-sm text-danger">{{ $errorMessage }}</p>
    @elseif ($hint)
        <p class="mt-2 text-sm text-muted">{{ $hint }}</p>
    @endif
</div>
