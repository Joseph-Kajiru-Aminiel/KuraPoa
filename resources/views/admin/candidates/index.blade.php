@extends('layouts.admin')

@section('title', __('Wagombea'))
@section('page-heading', __('Wagombea'))

@section('content')
    <x-section-title :title="__('Wagombea')" :description="__('Kagua na uidhinishe wagombea kabla ya kufungua uchaguzi.')" />
    <form method="GET" class="mt-6 grid gap-3 rounded-2xl border border-border bg-card p-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-input name="q" :label="__('Tafuta jina au namba ya usajili')" :value="request('q')" />
        <label class="text-sm font-semibold text-body">{{ __('Uchaguzi') }}
            <select name="election_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Chaguzi zote') }}</option>@foreach ($elections as $election)<option value="{{ $election->id }}" @selected(request('election_id') == $election->id)>{{ $election->title }}</option>@endforeach</select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Hali ya mgombea') }}
            <select name="status" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Hali zote') }}</option>@foreach (['pending' => __('Imeandaliwa'), 'approved' => __('Ameidhinishwa'), 'rejected' => __('Imekataliwa'), 'withdrawn' => __('Amejiondoa')] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Nafasi') }}
            <select name="position_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Nafasi zote') }}</option>@foreach ($elections as $election) @foreach ($election->positions as $position)<option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>{{ $election->title }} — {{ $position->title }}</option>@endforeach @endforeach</select>
        </label>
        <div class="lg:col-span-4"><x-button type="submit" variant="secondary">{{ __('Tafuta') }}</x-button></div>
    </form>

    @if ($candidates->isEmpty())
        <x-empty-state class="mt-6" :title="__('Hakuna wagombea')" :description="__('Wagombea watakaosajiliwa wataonekana hapa.')" />
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="sticky top-0 bg-page text-xs font-semibold uppercase text-muted"><tr><th class="p-4">{{ __('Mgombea') }}</th><th class="p-4">{{ __('Uchaguzi') }}</th><th class="p-4">{{ __('Nafasi') }}</th><th class="p-4">{{ __('Hali ya mgombea') }}</th><th class="p-4">{{ __('Kitendo') }}</th></tr></thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($candidates as $candidate)
                            <tr class="odd:bg-card even:bg-page/60 hover:bg-primary-light/50">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <x-candidate-avatar :candidate="$candidate" size="sm" />
                                        <div>
                                            <a class="font-bold text-primary hover:underline" href="{{ route('admin.candidates.show', [$candidate, 'institution_id' => request('institution_id')]) }}">{{ $candidate->name }}</a>
                                            <p class="text-xs text-muted">{{ $candidate->reg_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">{{ $candidate->position->election->title }}</td>
                                <td class="p-4">{{ $candidate->position->title }}</td>
                                <td class="p-4">{{ __('candidate.status.'.$candidate->status) }}</td>
                                <td class="p-4">
                                    @if ($candidate->position->election->status === 'draft')
                                        <div class="flex flex-wrap gap-2">
                                            @if ($candidate->status !== 'approved')
                                                <form method="POST" action="{{ route('admin.candidates.approve', [$candidate, 'institution_id' => request('institution_id')]) }}">@csrf<button class="min-h-11 px-2 text-primary underline">{{ __('Idhinisha') }}</button></form>
                                            @endif
                                            @if ($candidate->status !== 'rejected')
                                                <form method="POST" action="{{ route('admin.candidates.reject', [$candidate, 'institution_id' => request('institution_id')]) }}" class="flex items-center gap-2">@csrf<input name="rejection_reason" required maxlength="500" class="min-h-11 w-36 rounded-lg border-border text-xs" placeholder="{{ __('Sababu ya kukataa') }}"><button class="min-h-11 px-2 text-danger underline">{{ __('Kataa') }}</button></form>
                                            @endif
                                            @if ($candidate->status !== 'withdrawn')
                                                <form method="POST" action="{{ route('admin.candidates.withdraw', [$candidate, 'institution_id' => request('institution_id')]) }}">@csrf<button class="min-h-11 px-2 text-muted underline">{{ __('Ondoa') }}</button></form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $candidates->links() }}</div>
        </div>
    @endif
@endsection
