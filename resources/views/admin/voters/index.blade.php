@extends('layouts.admin')

@section('title', __('Wapiga kura'))
@section('page-heading', __('Wapiga kura'))

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <x-section-title :title="__('Wapiga kura')" :description="__('Thibitisha akaunti na ufuatilie ushiriki bila kuonyesha chaguo la mpiga kura.')" />
        <a href="{{ route('admin.voters.template') }}" class="min-h-11 inline-flex items-center font-semibold text-primary">{{ __('Pakua kiolezo cha CSV') }}</a>
    </div>

    <x-card class="mt-6">
        <h2 class="text-lg font-bold text-ink">{{ __('Sajili mpiga kura') }}</h2>
        <form method="POST" action="{{ route('admin.voters.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <label class="text-sm font-semibold text-body">{{ __('Uchaguzi') }}
                <select name="election_id" required class="mt-1 min-h-11 w-full rounded-xl border-border">
                    @foreach ($elections as $election)<option value="{{ $election->id }}">{{ $election->title }} @if($election->status !== 'draft') — {{ __('Umezimwa') }} @endif</option>@endforeach
                </select>
            </label>
            <x-input name="reg_number" :label="__('Namba ya usajili')" required />
            <x-input name="name" :label="__('Jina kamili')" required />
            <x-input name="phone" :label="__('Simu')" />
            <div class="sm:col-span-2"><x-button type="submit">{{ __('Sajili na pakua msimbo') }}</x-button></div>
        </form>
    </x-card>

    <form method="GET" class="mt-6 grid gap-3 rounded-2xl border border-border bg-card p-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-input name="q" :label="__('Tafuta jina au namba ya usajili')" :value="request('q')" />
        <label class="text-sm font-semibold text-body">{{ __('Uchaguzi') }}
            <select name="election_id" class="mt-1 min-h-11 w-full rounded-xl border-border">
                <option value="">{{ __('Chaguzi zote') }}</option>
                @foreach ($elections as $election)<option value="{{ $election->id }}" @selected(request('election_id') == $election->id)>{{ $election->title }}</option>@endforeach
            </select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Uthibitisho') }}
            <select name="verified" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Yote') }}</option><option value="1" @selected(request('verified') === '1')>{{ __('Waliothibitishwa') }}</option><option value="0" @selected(request('verified') === '0')>{{ __('Haijathibitishwa') }}</option></select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Hali ya akaunti') }}
            <select name="active" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Yote') }}</option><option value="1" @selected(request('active') === '1')>{{ __('Inatumika') }}</option><option value="0" @selected(request('active') === '0')>{{ __('Imezimwa') }}</option></select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Ushiriki') }}
            <select name="voted" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Yote') }}</option><option value="1" @selected(request('voted') === '1')>{{ __('Amepiga kura') }}</option><option value="0" @selected(request('voted') === '0')>{{ __('Hajapiga kura') }}</option></select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Idara') }}
            <select name="department_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Yote') }}</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Kozi') }}
            <select name="course_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Yote') }}</option>@foreach ($courses as $course)<option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>{{ $course->name }}</option>@endforeach</select>
        </label>
        <label class="text-sm font-semibold text-body">{{ __('Darasa') }}
            <select name="class_group_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Yote') }}</option>@foreach ($classGroups as $classGroup)<option value="{{ $classGroup->id }}" @selected(request('class_group_id') == $classGroup->id)>{{ $classGroup->name }}</option>@endforeach</select>
        </label>
        <div class="sm:col-span-2 lg:col-span-4"><x-button type="submit" variant="secondary">{{ __('Tafuta') }}</x-button></div>
    </form>

    @if ($voters->isEmpty())
        <x-empty-state class="mt-6" :title="__('Hakuna wapiga kura')" :description="__('Wapiga kura walioandikishwa wataonekana hapa.')" />
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-border bg-card shadow-soft">
                <form id="bulk-voters" method="POST" action="{{ route('admin.voters.bulk', request()->only('institution_id')) }}">@csrf</form>
                <div class="flex flex-wrap items-center gap-3 border-b border-border p-4">
                    <label class="sr-only" for="bulk-action">{{ __('Vitendo vya pamoja') }}</label>
                    <select id="bulk-action" name="action" form="bulk-voters" required class="min-h-11 rounded-xl border-border text-sm">
                        <option value="">{{ __('Chagua kitendo') }}</option>
                        <option value="verify">{{ __('Thibitisha') }}</option>
                        <option value="unverify">{{ __('Ondoa uthibitisho') }}</option>
                        <option value="activate">{{ __('Washa akaunti') }}</option>
                        <option value="deactivate">{{ __('Zima akaunti') }}</option>
                    </select>
                    <x-button type="submit" form="bulk-voters" variant="secondary">{{ __('Tekeleza kwa waliochaguliwa') }}</x-button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="sticky top-0 bg-page text-xs font-semibold uppercase text-muted"><tr>
                            <th class="p-4"><span class="sr-only">{{ __('Chagua') }}</span></th><th class="p-4">{{ __('Mpiga kura') }}</th><th class="p-4">{{ __('Uchaguzi') }}</th><th class="p-4">{{ __('Uthibitisho') }}</th><th class="p-4">{{ __('Hali ya akaunti') }}</th><th class="p-4">{{ __('Ushiriki') }}</th><th class="p-4">{{ __('Ustahiki') }}</th><th class="p-4">{{ __('Vitendo') }}</th>
                        </tr></thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($voters as $voter)
                                <tr class="odd:bg-card even:bg-page/60 hover:bg-primary-light/50">
                                    <td class="p-4"><input type="checkbox" form="bulk-voters" name="voter_ids[]" value="{{ $voter->id }}" class="rounded border-border text-primary focus:ring-primary" aria-label="{{ __('Chagua mpiga kura :name', ['name' => $voter->name]) }}"></td>
                                    <td class="p-4"><p class="font-bold text-ink">{{ $voter->name }}</p><p class="text-xs text-muted">{{ $voter->reg_number }}</p></td>
                                    <td class="p-4">{{ $voter->election->title }}</td>
                                    <td class="p-4">{{ $voter->verified_at ? __('Waliothibitishwa') : __('Haijathibitishwa') }}</td>
                                    <td class="p-4">{{ $voter->is_active ? __('Inatumika') : __('Imezimwa') }}</td>
                                    <td class="p-4">{{ $voter->has_voted ? __('Amepiga kura') : __('Hajapiga kura') }}</td>
                                    <td class="p-4">{{ $voter->verified_at && $voter->is_active && ! $voter->has_voted && $voter->election->isVotingOpen() ? __('Anastahili') : __('Hastahili') }}</td>
                                    <td class="p-4">
                                        @if ($voter->election->status === 'draft')
                                            <div class="flex flex-wrap gap-2">
                                                <form method="POST" action="{{ route('admin.voters.verify', [$voter, 'institution_id' => request('institution_id')]) }}">@csrf<input type="hidden" name="verified" value="{{ $voter->verified_at ? 0 : 1 }}"><button class="min-h-11 px-2 text-primary underline">{{ $voter->verified_at ? __('Ondoa uthibitisho') : __('Thibitisha') }}</button></form>
                                                <form method="POST" action="{{ route('admin.voters.active', [$voter, 'institution_id' => request('institution_id')]) }}">@csrf<input type="hidden" name="active" value="{{ $voter->is_active ? 0 : 1 }}"><button class="min-h-11 px-2 text-primary underline">{{ $voter->is_active ? __('Zima') : __('Washa') }}</button></form>
                                                @if (! $voter->has_voted)
                                                    <form method="POST" action="{{ route('admin.voters.regenerate', [$voter, 'institution_id' => request('institution_id')]) }}">@csrf<button class="min-h-11 px-2 text-primary underline">{{ __('Tengeneza msimbo mpya') }}</button></form>
                                                @endif
                                                <details class="w-full">
                                                    <summary class="min-h-11 cursor-pointer py-3 font-semibold text-primary">{{ __('Hariri taarifa') }}</summary>
                                                    <form method="POST" action="{{ route('admin.voters.update', [$voter, 'institution_id' => request('institution_id')]) }}" class="grid gap-3 rounded-xl bg-page p-3 sm:grid-cols-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <x-input name="reg_number" :label="__('Namba ya usajili')" :value="$voter->reg_number" required />
                                                        <x-input name="name" :label="__('Jina kamili')" :value="$voter->name" required />
                                                        <x-input name="phone" :label="__('Simu')" :value="$voter->phone" />
                                                        <label class="text-sm font-semibold text-body">{{ __('Idara') }}
                                                            <select name="department_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Hakuna taarifa bado') }}</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected($voter->department_id === $department->id)>{{ $department->faculty->name }} — {{ $department->name }}</option>@endforeach</select>
                                                        </label>
                                                        <label class="text-sm font-semibold text-body">{{ __('Kozi') }}
                                                            <select name="course_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Hakuna taarifa bado') }}</option>@foreach ($courses as $course)<option value="{{ $course->id }}" @selected($voter->course_id === $course->id)>{{ $course->department->name }} — {{ $course->name }}</option>@endforeach</select>
                                                        </label>
                                                        <label class="text-sm font-semibold text-body">{{ __('Darasa') }}
                                                            <select name="class_group_id" class="mt-1 min-h-11 w-full rounded-xl border-border"><option value="">{{ __('Hakuna taarifa bado') }}</option>@foreach ($classGroups as $classGroup)<option value="{{ $classGroup->id }}" @selected($voter->class_group_id === $classGroup->id)>{{ $classGroup->course->name }} — {{ $classGroup->name }} ({{ $classGroup->academicYear->name }})</option>@endforeach</select>
                                                        </label>
                                                        <div class="sm:col-span-2"><x-button type="submit" variant="secondary">{{ __('Hifadhi taarifa') }}</x-button></div>
                                                    </form>
                                                </details>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            <div class="p-4">{{ $voters->links() }}</div>
        </div>
    @endif
@endsection
