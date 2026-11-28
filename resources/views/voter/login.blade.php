@extends('layouts.voter-auth')

@section('title', __('Ingia kupiga kura'))

@section('content')
    @php
        $loginMessage = (string) $errors->first('login');
        $expiredSessionMessage = __('Kikao cha kupiga kura kimeisha. Ingia tena ili kuendelea.');
        $closedElectionMessage = __('Muda wa kupiga kura kwa uchaguzi huu umeisha au bado haujaanza.');
        $unverifiedMessage = __('Akaunti yako haijathibitishwa');
        $sessionExpired = $loginMessage === $expiredSessionMessage;
        $electionNotOpen = $loginMessage === $closedElectionMessage;
        $unverified = $loginMessage === $unverifiedMessage;
        $status = $sessionExpired
            ? __('Kikao kimeisha')
            : ($electionNotOpen ? __('Uchaguzi hauko wazi kwa sasa') : ($unverified ? __('Akaunti haijathibitishwa') : __('Salama')));
        $illustration = $sessionExpired || $electionNotOpen ? 'timeout' : ($unverified ? 'pending' : 'voter');
    @endphp

    @if ($elections->isEmpty())
        <x-auth-card
            status-variant="warning"
            illustration="timeout"
            :status="__('Hakuna uchaguzi ulio wazi')"
            :title="__('Hakuna uchaguzi ulio wazi')"
            :description="__('Kwa sasa hakuna uchaguzi unaopokea kura. Tafadhali rudi baadaye.')"
        >
            @if ($errors->any())
                <x-alert type="error" class="mb-4">{{ __($errors->first()) }}</x-alert>
            @endif
            <x-button :href="route('home')" variant="secondary" class="w-full">{{ __('Rudi mwanzo') }}</x-button>
        </x-auth-card>
    @else
        <x-auth-card
            :status="$status"
            :status-variant="$loginMessage === '' ? 'secure' : 'warning'"
            :illustration="$illustration"
            :title="__('Ingia kupiga kura')"
            :description="__('Tumia namba yako ya usajili na msimbo uliopewa na msimamizi.')"
        >
            <form method="POST" action="{{ route('voter.login.store') }}" class="space-y-4" x-data="formSubmitState()" @submit="submitOnce($event)">
                @csrf

                <div>
                    <label for="election_id" class="mb-2 block text-sm font-bold text-ink">{{ __('Chagua uchaguzi') }} <span aria-hidden="true" class="text-danger">*</span></label>
                    <select id="election_id" name="election_id" required aria-describedby="election-help" class="min-h-[46px] w-full rounded-xl border border-field-border bg-card-cream px-3.5 py-3 text-sm text-ink focus:border-forest focus:ring-forest">
                        <option value="">{{ __('Chagua uchaguzi unaotaka kupigia kura') }}</option>
                        @foreach ($elections as $election)
                            <option value="{{ $election->id }}" @selected(old('election_id') == $election->id)>{{ $election->title }}</option>
                        @endforeach
                    </select>
                    <p id="election-help" class="mt-1.5 text-xs leading-5 text-muted-ink">{{ __('Chagua uchaguzi unaotaka kupigia kura') }}</p>
                </div>

                <x-form-field
                    name="reg_number"
                    :label="__('Namba ya usajili')"
                    icon="registration"
                    :value="old('reg_number')"
                    required
                    autocomplete="username"
                    placeholder="MOCU/2026/001"
                    @input="$event.target.value = $event.target.value.toUpperCase().trim()"
                />

                <x-otp-input />

                <div class="flex min-h-8 items-center text-sm" aria-live="polite">
                    @if ($errors->any())
                        <span class="font-medium text-danger" role="alert">{{ __($errors->first()) }}</span>
                    @endif
                </div>

                <x-primary-button :loading-label="__('Inaingia...')">{{ __('Ingia kupiga kura') }}</x-primary-button>
            </form>

            <x-slot:note>
                <span class="inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1.7 3 4.3v4.4c0 4.4 2.8 7.5 7 9.6 4.2-2.1 7-5.2 7-9.6V4.3l-7-2.6Zm-3 7.8a1 1 0 0 1 1.4 0l1.1 1.1 2.2-2.2a1 1 0 1 1 1.4 1.4l-2.9 2.9a1 1 0 0 1-1.4 0L7 10.9a1 1 0 0 1 0-1.4Z" clip-rule="evenodd"/></svg>
                    {{ __('Chaguo lako ni siri. Taarifa zako za kuingia hazionyeshi ulichokichagua.') }}
                </span>
            </x-slot:note>
        </x-auth-card>
    @endif

    @if ($elections->isNotEmpty())
        <section class="mt-8" aria-labelledby="ongoing-elections-title">
            <h2 id="ongoing-elections-title" class="text-sm font-bold text-ink">{{ __('Chaguzi zinazoendelea') }}</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($elections as $election)
                    <li class="flex items-start gap-3 rounded-xl border border-divider bg-card-cream px-4 py-3">
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-ink">{{ $election->title }}</span>
                            <span class="mt-1 block text-xs text-muted-ink">{{ __('Mwisho: :date', ['date' => $election->ends_at->format('d/m/Y H:i')]) }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
