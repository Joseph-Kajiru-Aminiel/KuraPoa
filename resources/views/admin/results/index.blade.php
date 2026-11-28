@extends('layouts.admin')

@section('title', 'Matokeo')
@section('page-heading', 'Matokeo')

@section('content')
    <x-section-title title="Matokeo ya chaguzi" description="Matokeo yanaonekana baada ya uchaguzi kufungwa au muda wake kuisha. Mahudhurio pekee yanaonekana wakati uchaguzi unaendelea." />
    <div class="mt-7 space-y-4">
        @forelse ($elections as $election)
            <x-card class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-ink">{{ $election->title }}</h2>
                    <p class="mt-1 text-sm text-muted">
                        {{ number_format($election->voted_count) }} kati ya {{ number_format($election->voters_count) }} wamepiga kura
                        @unless ($election->isClosed())
                            · Matokeo bado yamefichwa
                        @endunless
                    </p>
                </div>
                <x-button variant="secondary" :href="route('admin.results.show', $election)">
                    {{ $election->isClosed() ? 'Ona matokeo' : 'Ona mahudhurio' }}
                </x-button>
            </x-card>
        @empty
            <x-empty-state title="Bado hakuna uchaguzi" description="Matokeo ya uchaguzi yataonekana hapa baada ya kufungwa." />
        @endforelse
        @if ($elections->hasPages())
            <div class="pt-2">{{ $elections->links() }}</div>
        @endif
    </div>
@endsection
