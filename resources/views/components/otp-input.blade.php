@props([
    'name' => 'access_code',
    'label' => __('Msimbo wa kupigia kura'),
])

<div x-data="accessCodeInput()">
    <fieldset>
        <legend class="mb-2 block text-sm font-bold text-ink">{{ $label }}</legend>
        <input type="hidden" name="{{ $name }}" :value="digits.join('')">
        <div class="flex items-center justify-center gap-1.5" role="group" aria-describedby="access-code-help">
            <div class="flex gap-1.5">
                @for ($index = 0; $index < 3; $index++)
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        data-code-index="{{ $index }}"
                        :value="digits[{{ $index }}]"
                        aria-label="{{ __('Tarakimu ya :number kati ya 6', ['number' => $index + 1]) }}"
                        @input="updateDigit({{ $index }}, $event)"
                        @keydown="handleKeydown({{ $index }}, $event)"
                        @paste="pasteCode($event, {{ $index }})"
                        autocomplete="{{ $index === 0 ? 'one-time-code' : 'off' }}"
                        @if ($index === 0) required @endif
                        class="h-[50px] w-9 min-w-0 rounded-xl border border-field-border bg-card-cream px-1 text-center text-lg font-extrabold tracking-wide text-forest focus:border-forest focus:ring-forest"
                    >
                @endfor
            </div>
            <span class="w-2 text-center font-bold text-muted-ink" aria-hidden="true">–</span>
            <div class="flex gap-1.5">
                @for ($index = 3; $index < 6; $index++)
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        data-code-index="{{ $index }}"
                        :value="digits[{{ $index }}]"
                        aria-label="{{ __('Tarakimu ya :number kati ya 6', ['number' => $index + 1]) }}"
                        @input="updateDigit({{ $index }}, $event)"
                        @keydown="handleKeydown({{ $index }}, $event)"
                        @paste="pasteCode($event, {{ $index }})"
                        autocomplete="off"
                        class="h-[50px] w-9 min-w-0 rounded-xl border border-field-border bg-card-cream px-1 text-center text-lg font-extrabold tracking-wide text-forest focus:border-forest focus:ring-forest"
                    >
                @endfor
            </div>
        </div>
        <p id="access-code-help" class="mt-2 text-center text-xs leading-5 text-muted-ink">
            {{ __('Ingiza msimbo wa tarakimu sita. Unaweza pia kuubandika.') }}
        </p>
    </fieldset>
</div>
