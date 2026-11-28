@extends('layouts.public')

@section('title', 'Matokeo | '.$election->title)

@section('content')
    <header class="border-b border-border bg-card print:hidden">
        <nav class="mx-auto flex min-h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <x-logo />
            <x-language-switcher />
            <x-button variant="secondary" :href="route('home')">{{ __('Rudi mwanzo') }}</x-button>
        </nav>
    </header>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <x-section-title :eyebrow="__('Matokeo rasmi')" :title="$election->title" :description="__('Matokeo ya kila nafasi na mahudhurio ya wapiga kura.')" />
            <x-button type="button" class="print:hidden" onclick="window.print()">{{ __('Chapisha / Hifadhi PDF') }}</x-button>
        </div>

        <x-card class="mt-7">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-muted">{{ __('Mahudhurio ya wapiga kura') }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-ink">{{ number_format($results['turnout']['voted']) }} <span class="text-lg font-semibold text-muted">/ {{ number_format($results['turnout']['registered']) }}</span></p>
                </div>
                <x-badge variant="primary">{{ __(':percent% wamepiga kura', ['percent' => number_format($results['turnout']['percentage'], 1)]) }}</x-badge>
            </div>
        </x-card>

        <div class="mt-6 space-y-6">
            @forelse ($results['positions'] as $position)
                <x-card>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-ink">{{ $position->title }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ __('Jumla ya kura: :count', ['count' => number_format($position->total_votes)]) }}</p>
                        </div>
                        @if ($position->is_tied)
                            <x-badge variant="warning">{{ __('Sare ya kura') }}</x-badge>
                        @endif
                    </div>
                    <div class="mt-6 space-y-5">
                        @forelse ($position->candidates as $candidate)
                            <div>
                                <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <x-candidate-avatar :candidate="$candidate" size="sm" />
                                        <span class="font-semibold text-ink">{{ $candidate->name }}</span>
                                        @if ($candidate->is_winner)
                                            <x-badge variant="success">{{ __('Mshindi') }}</x-badge>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-body">{{ number_format($candidate->votes_count) }} · {{ number_format($candidate->percentage, 1) }}%</span>
                                </div>
                                <div class="h-3 overflow-hidden rounded-full bg-page" role="progressbar" aria-label="{{ __('Asilimia ya kura za :name', ['name' => $candidate->name]) }}" aria-valuenow="{{ $candidate->percentage }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="h-full rounded-full {{ $candidate->is_leader ? 'bg-primary' : 'bg-muted' }}" style="width: {{ min(100, $candidate->percentage) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-muted">{{ __('Hakuna wagombea katika nafasi hii.') }}</p>
                        @endforelse
                    </div>
                </x-card>
            @empty
                <x-empty-state :title="__('Hakuna nafasi za uchaguzi')" :description="__('Hakuna nafasi zilizowekwa kwenye uchaguzi huu.')" />
            @endforelse
        </div>
    </main>
    <footer class="border-t border-border bg-card py-6 text-center text-sm text-muted print:hidden">{{ __('Kura yako, sauti yako.') }} · KuraPoa</footer>
@endsection
