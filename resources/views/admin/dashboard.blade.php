@extends('layouts.admin')

@section('title', __('Dashibodi'))
@section('page-heading', __('Dashibodi'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <x-section-title :title="__('Karibu, :name', ['name' => auth()->user()->name])" :description="__('Muhtasari wa shughuli za uchaguzi.')" />
        <x-button :href="route('admin.elections.create')">{{ __('Unda uchaguzi') }}</x-button>
    </div>

    <section class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Takwimu za jumla') }}">
        <x-stat-card :label="__('Chaguzi')" :value="number_format($stats['elections'])" />
        <x-stat-card :label="__('Wagombea')" :value="number_format($stats['candidates'])" />
        <x-stat-card :label="__('Wapiga kura')" :value="number_format($stats['voters'])" />
        <x-stat-card :label="__('Waliopiga kura')" :value="number_format($stats['voted'])" />
    </section>

    <section class="mt-8 rounded-2xl border border-border bg-card p-5 shadow-soft sm:p-6" aria-labelledby="turnout-heading">
        <h2 id="turnout-heading" class="text-lg font-bold text-ink">{{ __('Ushiriki katika chaguzi za hivi karibuni') }}</h2>
        @if ($elections->isEmpty())
            <p class="mt-4 text-sm text-muted">{{ __('Takwimu za ushiriki zitaonekana baada ya kuundwa kwa uchaguzi.') }}</p>
        @else
            <div class="mt-5 space-y-4">
                @foreach ($elections as $election)
                    @php
                        $turnout = $election->voters_count > 0 ? round($election->voted_count / $election->voters_count * 100) : 0;
                    @endphp
                    <div class="grid gap-2 sm:grid-cols-[minmax(10rem,1fr)_2fr_auto] sm:items-center">
                        <p class="truncate text-sm font-medium text-body">{{ $election->title }}</p>
                        <div class="h-3 overflow-hidden rounded-full bg-page" role="progressbar" aria-label="{{ __('Ushiriki wa :title', ['title' => $election->title]) }}" aria-valuenow="{{ $turnout }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full rounded-full bg-primary transition-all duration-200" style="width: {{ $turnout }}%"></div>
                        </div>
                        <p class="text-sm font-bold text-ink">{{ $turnout }}%</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-8">
        <div class="mb-4 flex items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-ink">{{ __('Chaguzi za hivi karibuni') }}</h2>
            <a href="{{ route('admin.elections.index') }}" class="min-h-11 inline-flex items-center text-sm font-semibold text-primary hover:underline">{{ __('Ona zote') }}</a>
        </div>
        @if ($elections->isEmpty())
            <x-empty-state :title="__('Bado hakuna uchaguzi')" :description="__('Unda uchaguzi wa kwanza ili uanze kupanga nafasi na wapiga kura.')">
                <x-button :href="route('admin.elections.create')">{{ __('Unda uchaguzi') }}</x-button>
            </x-empty-state>
        @else
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="bg-page text-xs font-semibold uppercase tracking-wide text-muted">
                            <tr>
                                <th scope="col" class="px-5 py-4">{{ __('Uchaguzi') }}</th>
                                <th scope="col" class="px-5 py-4">{{ __('Tarehe ya mwisho') }}</th>
                                <th scope="col" class="px-5 py-4">{{ __('Hali') }}</th>
                                <th scope="col" class="px-5 py-4">{{ __('Waliopiga kura') }}</th>
                                <th scope="col" class="px-5 py-4"><span class="sr-only">{{ __('Vitendo') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($elections as $election)
                                @php
                                    $status = $election->isClosed() ? 'closed' : $election->status;
                                    $variant = ['draft' => 'neutral', 'scheduled' => 'warning', 'open' => 'success', 'closed' => 'dark'][$status];
                                    $label = ['draft' => __('Rasimu'), 'scheduled' => __('Imepangwa'), 'open' => __('Wazi'), 'closed' => __('Imefungwa')][$status];
                                @endphp
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-ink">{{ $election->title }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-body">{{ $election->ends_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4"><x-badge :variant="$variant">{{ $label }}</x-badge></td>
                                    <td class="px-5 py-4 text-body">{{ number_format($election->voted_count) }} / {{ number_format($election->voters_count) }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('admin.elections.show', $election) }}" class="font-semibold text-primary hover:underline">{{ __('Fungua') }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
@endsection
