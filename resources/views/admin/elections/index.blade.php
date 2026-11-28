@extends('layouts.admin')

@section('title', 'Chaguzi')
@section('page-heading', 'Chaguzi')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <x-section-title title="Chaguzi" description="Unda na simamia chaguzi za wanafunzi." />
        <x-button :href="route('admin.elections.create')">Unda uchaguzi</x-button>
    </div>

    <div class="mt-7">
        @if ($elections->isEmpty())
            <x-empty-state title="Bado hakuna uchaguzi" description="Uchaguzi utakaounda utaonekana hapa.">
                <x-button :href="route('admin.elections.create')">Unda uchaguzi wa kwanza</x-button>
            </x-empty-state>
        @else
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-page text-xs font-semibold uppercase tracking-wide text-muted">
                            <tr>
                                <th scope="col" class="px-5 py-4">Jina la uchaguzi</th>
                                <th scope="col" class="px-5 py-4">Nafasi</th>
                                <th scope="col" class="px-5 py-4">Wapiga kura</th>
                                <th scope="col" class="px-5 py-4">Hali</th>
                                <th scope="col" class="px-5 py-4">Mwisho</th>
                                <th scope="col" class="px-5 py-4"><span class="sr-only">Vitendo</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($elections as $election)
                                @php
                                    $status = $election->isClosed() ? 'closed' : $election->status;
                                    $variant = ['draft' => 'neutral', 'scheduled' => 'warning', 'open' => 'success', 'closed' => 'dark'][$status];
                                    $label = ['draft' => 'Rasimu', 'scheduled' => 'Imepangwa', 'open' => 'Wazi', 'closed' => 'Imefungwa'][$status];
                                @endphp
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-ink">{{ $election->title }}</td>
                                    <td class="px-5 py-4 text-body">{{ $election->positions_count }}</td>
                                    <td class="px-5 py-4 text-body">{{ number_format($election->voted_count) }} / {{ number_format($election->voters_count) }}</td>
                                    <td class="px-5 py-4"><x-badge :variant="$variant">{{ $label }}</x-badge></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-body">{{ $election->ends_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('admin.elections.show', $election) }}" class="font-semibold text-primary hover:underline">Simamia</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-border px-5 py-4">{{ $elections->links() }}</div>
            </div>
        @endif
    </div>
@endsection
