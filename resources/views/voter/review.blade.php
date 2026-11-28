@extends('layouts.voter-ballot')

@section('title', __('Hakiki chaguo lako'))
@section('election-title', $election->title)

@section('institution-logo')
    @if ($election->institution?->logo)
        <img src="{{ asset('storage/'.$election->institution->logo) }}" alt="{{ __('Nembo ya :name', ['name' => $election->institution->name]) }}" class="h-full w-full object-contain p-1">
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
    @if ($election->is_test)
        <x-status-badge variant="warning" class="min-h-6 px-2 py-0 text-[10px]">{{ __('Mazingira ya majaribio') }}</x-status-badge>
    @endif
@endsection

@section('content')
    <div class="mx-auto max-w-3xl">
        <header class="mb-6">
            <p class="text-xs font-bold uppercase tracking-wide text-forest">{{ __('Kagua kura yako') }}</p>
            <h1 class="mt-1 text-2xl font-extrabold text-ink sm:text-3xl">{{ __('Hakiki chaguo lako') }}</h1>
            <p class="mt-2 text-sm leading-6 text-muted-ink">{{ __('Kagua kwa makini mgombea uliyemchagua kwa kila nafasi.') }}</p>
        </header>

        <div class="space-y-3">
            @foreach ($positions as $position)
                <article class="rounded-2xl border border-divider bg-card-cream p-4 shadow-soft sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-muted-ink">{{ __('Nafasi :current kati ya :total', ['current' => $position->step_index + 1, 'total' => $positions->count()]) }}</p>
                            <h2 class="mt-1 text-lg font-extrabold text-ink">{{ $position->title }}</h2>
                            <div class="mt-3 flex items-start gap-3">
                                <x-candidate-avatar :candidate="$position->selected_candidate" size="md" class="!h-16 !w-16 !rounded-xl sm:!h-20 sm:!w-20" />
                                <div class="min-w-0">
                                    <p class="font-bold text-ink">{{ $position->selected_candidate->name }}</p>
                                    <p class="mt-1 text-sm leading-6 text-body">{{ $position->selected_candidate->manifesto ?: __('Mgombea bado hajaweka ilani.') }}</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('voter.ballot.restore') }}" class="shrink-0">
                            @csrf
                            @foreach ($positions as $choicePosition)
                                <input type="hidden" name="choices[{{ $choicePosition->id }}]" value="{{ $choices[$choicePosition->id] }}">
                            @endforeach
                            <input type="hidden" name="return_step" value="{{ $position->step_index }}">
                            <x-button type="submit" variant="secondary" class="w-full sm:w-auto">{{ __('Badilisha') }}</x-button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-warning bg-cream px-4 py-4 text-sm leading-6 text-ink" role="note">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-ink" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.1 2.8a1 1 0 0 1 1.8 0l7 13.2a1 1 0 0 1-.9 1.5H3a1 1 0 0 1-.9-1.5l7-13.2ZM10 7a1 1 0 0 0-1 1v3a1 1 0 1 0 2 0V8a1 1 0 0 0-1-1Zm0 7.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
            <p>{{ __('Ukithibitisha, kura yako itatumwa na hutaweza kubadili chaguo lako.') }}</p>
        </div>

        <div class="safe-bottom sticky bottom-0 z-20 -mx-4 mt-4 border-t border-divider bg-card-cream/95 px-4 pt-3 backdrop-blur sm:mx-0 sm:rounded-2xl sm:border sm:p-4">
            <div class="mx-auto grid max-w-3xl items-end gap-3 sm:grid-cols-2">
                <form method="POST" action="{{ route('voter.submit') }}" class="space-y-2" x-data="formSubmitState()" @submit="submitOnce($event)">
                    @csrf
                    @foreach ($positions as $choicePosition)
                        <input type="hidden" name="choices[{{ $choicePosition->id }}]" value="{{ $choices[$choicePosition->id] }}">
                    @endforeach

                    <label for="confirm-vote" class="flex min-h-12 cursor-pointer items-center gap-3 text-sm font-semibold text-ink">
                        <input id="confirm-vote" type="checkbox" required class="h-5 w-5 shrink-0 rounded border-field-border text-forest focus:ring-forest">
                        {{ __('Nathibitisha kuwa haya ndiyo machaguo yangu na niko tayari kutuma kura.') }}
                    </label>

                    <x-primary-button :loading-label="__('Inatuma kura...')">{{ __('Thibitisha na Tuma Kura') }}</x-primary-button>
                </form>

                <form method="POST" action="{{ route('voter.ballot.restore') }}" class="sm:pb-0.5">
                    @csrf
                    @foreach ($positions as $position)
                        <input type="hidden" name="choices[{{ $position->id }}]" value="{{ $choices[$position->id] }}">
                    @endforeach
                    <input type="hidden" name="return_step" value="0">
                    <x-button type="submit" variant="secondary" class="w-full">{{ __('Rudi kuhariri chaguo') }}</x-button>
                </form>
            </div>
        </div>
    </div>
@endsection
