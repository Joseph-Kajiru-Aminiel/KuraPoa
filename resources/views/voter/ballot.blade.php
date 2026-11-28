@extends('layouts.voter-ballot')

@section('title', __('Kura yako'))
@section('election-title', $voter->election->title)

@section('institution-logo')
    @if ($voter->election->institution?->logo)
        <img src="{{ asset('storage/'.$voter->election->institution->logo) }}" alt="{{ __('Nembo ya :name', ['name' => $voter->election->institution->name]) }}" class="h-full w-full object-contain p-1">
    @else
        <svg class="h-7 w-7 text-forest" viewBox="0 0 40 40" fill="none" aria-hidden="true">
            <path d="M14 6.5A2.5 2.5 0 0 1 16.5 4h8A2.5 2.5 0 0 1 27 6.5V25H14V6.5Z" fill="#E6F4EE" stroke="currentColor" stroke-width="1.5"/>
            <path d="m17 14 2.4 2.4L24 11.5" stroke="#D9A404" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="6" y="18" width="28" height="17" rx="4" fill="currentColor"/>
            <path d="M6 22h28M13 18l3-3h8l3 3" stroke="#0B3D2E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    @endif
@endsection

@section('test-badge')
    @if ($voter->election->is_test)
        <x-status-badge variant="warning" class="min-h-6 px-2 py-0 text-[10px]">{{ __('Mazingira ya majaribio') }}</x-status-badge>
    @endif
@endsection

@section('content')
    <div
        x-data="ballotWizard(@js($ballotData), @js($initialChoices), @js($initialStep), @js(__('Nafasi :current kati ya :total')))"
        data-initial-step="{{ $initialStep }}"
        @keydown.escape.window="closeManifesto()"
    >
        <div class="rounded-2xl border border-divider bg-card-cream p-4 shadow-soft sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-xl font-extrabold text-ink sm:text-2xl">{{ __('Chagua viongozi wako') }}</h1>
                <span class="rounded-full border border-primary/30 bg-primary-light px-3 py-1.5 text-xs font-bold text-forest" aria-live="polite" x-text="positionText()"></span>
            </div>

            <ol class="mt-5 flex items-center" aria-label="{{ __('Hatua za kupiga kura') }}">
                <template x-for="(position, index) in positions" :key="position.id">
                    <li class="flex min-w-0 flex-1 items-center">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border text-xs font-bold transition-colors duration-150"
                            :class="index <= current ? 'border-forest bg-forest text-white' : 'border-field-border bg-card-cream text-muted-ink'"
                            :aria-current="index === current ? 'step' : null"
                            x-text="index + 1"
                        ></span>
                        <span x-show="index < positions.length - 1" class="mx-1.5 h-0.5 min-w-2 flex-1 rounded-full sm:mx-2" :class="index < current ? 'bg-forest' : 'bg-divider'" aria-hidden="true"></span>
                    </li>
                </template>
            </ol>

            <div class="mt-3 h-2 overflow-hidden rounded-full bg-divider" role="progressbar" :aria-label="@js(__('Maendeleo ya kupiga kura'))" :aria-valuenow="current + 1" aria-valuemin="1" :aria-valuemax="positions.length">
                <div class="h-full rounded-full bg-forest transition-all duration-200" :style="`width: ${((current + 1) / positions.length) * 100}%`"></div>
            </div>
        </div>

        <form x-ref="reviewForm" method="POST" action="{{ route('voter.review') }}">
            @csrf
            <template x-for="position in positions" :key="position.id">
                <input type="hidden" :name="`choices[${position.id}]`" :value="choices[position.id] || ''">
            </template>
        </form>

        <template x-for="(position, index) in positions" :key="position.id">
            <section x-show="current === index" x-cloak class="mt-6" :aria-labelledby="`position-title-${position.id}`">
                <h2 :id="`position-title-${position.id}`" tabindex="-1" class="mb-4 text-lg font-extrabold text-ink focus:outline-none sm:text-xl" x-text="position.title"></h2>
                <fieldset :aria-labelledby="`position-title-${position.id}`">
                    <legend class="sr-only" x-text="position.title"></legend>
                    <div class="grid gap-3 md:grid-cols-2">
                        <template x-for="candidate in position.candidates" :key="candidate.id">
                            <article class="rounded-2xl border border-divider bg-card-cream p-3 transition-colors duration-150 sm:p-4">
                                <label class="relative block cursor-pointer">
                                    <input
                                        type="radio"
                                        class="peer sr-only"
                                        :name="`position-choice-${position.id}`"
                                        :value="candidate.id"
                                        :checked="choices[position.id] == candidate.id"
                                        @change="select(position.id, candidate.id)"
                                    >
                                    <span
                                        class="relative flex min-h-28 items-center gap-3 rounded-xl border-2 p-3 transition duration-150 peer-focus-visible:ring-2 peer-focus-visible:ring-forest peer-focus-visible:ring-offset-2 sm:gap-4 sm:p-4"
                                        :class="choices[position.id] == candidate.id ? 'border-forest bg-primary-light shadow-[inset_0_0_0_1px_#0B3D2E]' : 'border-field-border bg-white hover:border-forest'"
                                    >
                                        <template x-if="candidate.photo">
                                            <img :src="candidate.photo" :alt="'{{ __('Picha ya :name') }}'.replace(':name', candidate.name)" loading="lazy" class="h-20 w-20 shrink-0 rounded-xl border border-divider bg-cream object-cover">
                                        </template>
                                        <template x-if="!candidate.photo">
                                            <span aria-hidden="true" class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-forest text-xl font-extrabold text-white" x-text="initials(candidate.name)"></span>
                                        </template>
                                        <span class="min-w-0 flex-1 pr-1">
                                            <span class="block text-base font-extrabold leading-5 text-ink" x-text="candidate.name"></span>
                                            <span class="mt-1 block text-xs font-semibold text-muted-ink" x-text="'{{ __('Mgombea wa :position') }}'.replace(':position', position.title)"></span>
                                            <span class="mt-2 block text-sm leading-5 text-body line-clamp-2" x-text="candidate.manifesto || @js(__('Mgombea bado hajaweka ilani.'))"></span>
                                        </span>
                                        <span x-show="choices[position.id] == candidate.id" x-cloak class="absolute right-2 top-2 inline-flex min-h-7 items-center gap-1 rounded-full bg-accent px-2 text-[10px] font-extrabold text-ink">
                                            <span aria-hidden="true">✓</span> {{ __('Umechagua') }}
                                        </span>
                                    </span>
                                </label>
                                <button
                                    type="button"
                                    class="mt-2 inline-flex min-h-11 items-center rounded-lg px-2 text-xs font-bold text-forest underline-offset-4 hover:underline focus-visible:ring-forest"
                                    @click="openManifesto(candidate, $event.currentTarget)"
                                    :aria-label="'{{ __('Soma ilani ya :name') }}'.replace(':name', candidate.name)"
                                >
                                    {{ __('Soma ilani') }}
                                </button>
                            </article>
                        </template>
                    </div>
                </fieldset>
            </section>
        </template>

        <p x-show="showError" x-cloak class="mt-4 rounded-xl border border-danger/30 bg-card-cream px-4 py-3 text-sm font-semibold text-danger" role="alert">
            {{ __('Chagua mgombea mmoja kabla ya kuendelea.') }}
        </p>

        <div class="safe-bottom sticky bottom-0 z-20 -mx-4 mt-6 border-t border-divider bg-card-cream/95 px-4 pt-3 backdrop-blur sm:mx-0 sm:rounded-2xl sm:border sm:p-4">
            <div class="mx-auto flex max-w-3xl items-center justify-between gap-3">
                <x-button type="button" variant="secondary" x-show="current > 0" x-cloak @click="current--">{{ __('Rudi nyuma') }}</x-button>
                <span x-show="current === 0" class="hidden sm:block"></span>
                <x-button type="button" x-show="current < positions.length - 1" x-cloak @click="continueToNextPosition()">{{ __('Endelea') }}</x-button>
                <x-button type="button" x-show="current === positions.length - 1" x-cloak @click="submitReview($refs.reviewForm)">{{ __('Kagua chaguo') }}</x-button>
            </div>
            <p class="mx-auto mt-3 max-w-3xl text-center text-xs leading-5 text-muted-ink">{{ __('Chaguo lako ni siri. Hakikisha umechagua mgombea mmoja kwa kila nafasi.') }}</p>
        </div>

        <div x-show="manifestoCandidate" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-ink/60 sm:items-center sm:p-5" @click.self="closeManifesto()">
            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="manifesto-dialog-title"
                x-transition.opacity.duration.200ms
                class="relative max-h-[85vh] w-full overflow-y-auto rounded-t-3xl border border-divider bg-card-cream p-5 shadow-soft sm:max-w-xl sm:rounded-2xl sm:p-7"
            >
                <button x-ref="manifestoClose" type="button" class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-xl text-forest hover:bg-cream focus-visible:ring-forest" @click="closeManifesto()" aria-label="{{ __('Funga') }}">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.2 4.2a1 1 0 0 1 1.4 0L10 8.6l4.4-4.4a1 1 0 1 1 1.4 1.4L11.4 10l4.4 4.4a1 1 0 0 1-1.4 1.4L10 11.4l-4.4 4.4a1 1 0 0 1-1.4-1.4L8.6 10 4.2 5.6a1 1 0 0 1 0-1.4Z" clip-rule="evenodd"/></svg>
                </button>
                <p class="text-xs font-bold uppercase tracking-wide text-muted-ink">{{ __('Ilani ya mgombea') }}</p>
                <h2 id="manifesto-dialog-title" class="mt-2 pr-12 text-xl font-extrabold text-ink" x-text="manifestoCandidate ? manifestoCandidate.name : ''"></h2>
                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-body" x-text="manifestoCandidate ? (manifestoCandidate.manifesto || @js(__('Mgombea bado hajaweka ilani.'))) : ''"></p>
            </section>
        </div>
    </div>
@endsection
