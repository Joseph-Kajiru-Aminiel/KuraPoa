@extends('layouts.admin')

@section('title', __('Kumbukumbu za ukaguzi'))
@section('page-heading', __('Kumbukumbu za ukaguzi'))

@section('content')
    <x-section-title :title="__('Kumbukumbu za ukaguzi')" :description="__('Historia ya vitendo vya usimamizi; kura za wagombea hazionyeshwi hapa.')" />
    <form method="GET" class="mt-6 flex flex-col gap-3 rounded-2xl border border-border bg-card p-4 sm:flex-row sm:items-end">
        <x-input name="action" :label="__('Tafuta kitendo')" :value="request('action')" />
        <x-button type="submit" variant="secondary">{{ __('Tafuta') }}</x-button>
    </form>

    @if ($logs->isEmpty())
        <x-empty-state class="mt-6" :title="__('Hakuna kumbukumbu')" :description="__('Vitendo vya usimamizi vitaonekana hapa.')" />
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-page text-xs font-semibold uppercase text-muted"><tr><th class="p-4">{{ __('Kitendo') }}</th><th class="p-4">{{ __('Msimamizi') }}</th><th class="p-4">{{ __('Tarehe') }}</th></tr></thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($logs as $log)
                            <tr><td class="p-4 font-semibold text-ink">{{ $log->action }}</td><td class="p-4 text-body">{{ $log->user?->name ?? __('Mfumo') }}</td><td class="p-4 text-muted">{{ $log->created_at?->locale(app()->getLocale())->isoFormat('LLL') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $logs->links() }}</div>
        </div>
    @endif
@endsection
