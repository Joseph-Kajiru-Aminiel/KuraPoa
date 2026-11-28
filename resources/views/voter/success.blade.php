@extends('layouts.voter-ballot')

@section('title', __('Kura imepokelewa'))

@section('content')
    <div class="mx-auto max-w-xl">
        <x-auth-card
            status-variant="success"
            illustration="verified"
            :status="__('Kura imepokelewa')"
            :title="__('Kura yako imepokelewa')"
            :description="__('Asante kwa kushiriki. Kura yako imehifadhiwa kwa siri na haiwezi kuhusishwa na chaguo lako.')"
        >
            <div x-data="receiptCopy(@js($reference), {
                copy: @js(__('Nakili')),
                copied: @js(__('Imenakili')),
                failed: @js(__('Hatujaweza kunakili namba. Tafadhali ihifadhi kwa mkono.')),
            })">
                <div class="rounded-2xl border border-forest bg-forest px-4 py-5 text-center sm:px-6">
                    <p class="text-xs font-bold uppercase tracking-wide text-white/80">{{ __('Namba ya kumbukumbu') }}</p>
                    <p class="mt-2 break-all font-mono text-2xl font-extrabold tracking-[0.16em] text-white sm:text-3xl">{{ $reference }}</p>
                    <p class="mt-2 text-xs leading-5 text-white/80">{{ __('Hifadhi namba hii kwa kumbukumbu. Haionyeshi wagombea uliowachagua.') }}</p>
                </div>

                <button
                    type="button"
                    class="mt-3 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-accent bg-accent px-4 py-3 text-sm font-extrabold text-ink transition-colors duration-150 hover:bg-accent-hover focus-visible:ring-forest"
                    @click="copy()"
                    x-text="copyState === 'copied' ? labels.copied : labels.copy"
                ></button>
                <p class="mt-2 min-h-5 text-center text-xs text-danger" aria-live="polite" x-text="copyState === 'failed' ? labels.failed : (copyState === 'copied' ? labels.copied : '')"></p>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 print:hidden">
                <x-button type="button" variant="secondary" class="w-full" onclick="window.print()">{{ __('Chapisha uthibitisho') }}</x-button>
                <x-button :href="route('home')" class="w-full">{{ __('Rudi mwanzo') }}</x-button>
            </div>

            <x-slot:note>
                <span class="inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5 8V6a5 5 0 0 1 10 0v2h.5A1.5 1.5 0 0 1 17 9.5v7a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 16.5v-7A1.5 1.5 0 0 1 4.5 8H5Zm2 0h6V6a3 3 0 1 0-6 0v2Z" clip-rule="evenodd"/></svg>
                    {{ __('Kura yako ni siri. Namba hii ya kumbukumbu haionyeshi chaguo lako.') }}
                </span>
            </x-slot:note>
        </x-auth-card>
    </div>
@endsection
