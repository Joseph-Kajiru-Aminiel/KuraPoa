@extends('layouts.admin')

@section('title', __('Wasifu wa mgombea'))
@section('page-heading', __('Wagombea'))

@section('content')
    <x-section-title :title="$candidate->name" :description="$candidate->position->election->title.' · '.$candidate->position->title" />
    <x-card class="mt-6">
        <div class="flex flex-col gap-5 sm:flex-row">
            <x-candidate-avatar :candidate="$candidate" size="xl" full-photo />
            <div class="space-y-2">
                <p><strong>{{ __('Namba ya usajili') }}:</strong> {{ $candidate->reg_number }}</p>
                <p><strong>{{ __('Hali ya mgombea') }}:</strong> {{ __('candidate.status.'.$candidate->status) }}</p>
                <p><strong>{{ __('Idara') }}:</strong> {{ $candidate->department?->name ?? '—' }}</p>
                <p class="max-w-3xl leading-7 text-body">{{ $candidate->manifesto }}</p>
                @if ($candidate->rejection_reason)<x-alert type="warning">{{ __('Sababu ya kukataa') }}: {{ $candidate->rejection_reason }}</x-alert>@endif
            </div>
        </div>
    </x-card>
    <x-card class="mt-6">
        <h2 class="text-lg font-bold text-ink">{{ __('Historia ya hali') }}</h2>
        <ol class="mt-4 space-y-3">
            @forelse ($history as $entry)
                <li class="rounded-xl bg-page p-3"><span class="font-semibold">{{ $entry->action }}</span><span class="ml-2 text-sm text-muted">{{ $entry->created_at?->locale(app()->getLocale())->isoFormat('LLL') }}</span></li>
            @empty
                <li class="text-sm text-muted">{{ __('Hakuna historia bado.') }}</li>
            @endforelse
        </ol>
    </x-card>
@endsection
