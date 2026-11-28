@extends('layouts.admin')

@section('title', 'Matokeo | '.$election->title)
@section('page-heading', 'Matokeo')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <x-section-title :title="$election->title" :description="__('Matokeo ya mwisho na muhtasari wa mahudhurio.')" />
        @if ($results)
            <x-button type="button" class="print:hidden" onclick="window.print()">{{ __('Chapisha / Hifadhi PDF') }}</x-button>
        @endif
    </div>

    <x-card class="mt-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-muted">{{ __('Mahudhurio ya wapiga kura') }}</p>
                <p class="mt-2 text-3xl font-extrabold text-ink">{{ number_format($turnout['voted']) }} <span class="text-lg font-semibold text-muted">/ {{ number_format($turnout['registered']) }}</span></p>
            </div>
            <x-badge variant="primary">{{ __(':percent% wamepiga kura', ['percent' => number_format($turnout['percentage'], 1)]) }}</x-badge>
        </div>
    </x-card>

    @if (! $results)
        <x-empty-state class="mt-6" :title="__('Matokeo bado yamefichwa')" :description="__('Idadi ya waliojitokeza inaonekana kwa wasimamizi, lakini kura na matokeo yatafunguliwa uchaguzi ukifungwa au muda wake ukiisha.')" />
    @else
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
    @endif
@endsection
