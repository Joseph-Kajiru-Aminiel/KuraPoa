@extends('layouts.admin')

@section('title', __('Muundo wa taasisi'))
@section('page-heading', __('Muundo wa taasisi'))

@section('content')
    <x-section-title :title="__('Muundo wa taasisi')" :description="$institution->name" />
    @php
        $labels = [
            'faculties' => __('Vitivo'),
            'departments' => __('Idara'),
            'courses' => __('Kozi'),
            'academic-years' => __('Miaka ya masomo'),
            'class-groups' => __('Madarasa'),
        ];
    @endphp
    <div class="mt-6 space-y-6">
        @foreach ($labels as $resource => $label)
            <x-card>
                <h2 class="text-lg font-bold text-ink">{{ $label }}</h2>
                <form method="POST" action="{{ route('admin.institution.structure.store', ['resource' => $resource, 'institution_id' => $institution->id]) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <x-input name="name" :label="__('Jina')" required />
                    @if (in_array($resource, ['faculties', 'departments', 'courses'], true))
                        <x-input name="code" :label="__('Msimbo')" required />
                    @endif
                    @if ($resource === 'departments')
                        <label class="text-sm font-semibold text-body">{{ __('Kitivo') }}
                            <select name="faculty_id" required class="mt-1 min-h-11 w-full rounded-xl border-border">
                                @foreach ($faculties as $faculty)<option value="{{ $faculty->id }}">{{ $faculty->name }}</option>@endforeach
                            </select>
                        </label>
                    @elseif ($resource === 'courses')
                        <label class="text-sm font-semibold text-body">{{ __('Idara') }}
                            <select name="department_id" required class="mt-1 min-h-11 w-full rounded-xl border-border">
                                @foreach ($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                            </select>
                        </label>
                        <x-input name="duration_years" :label="__('Muda wa masomo (miaka)')" type="number" min="1" max="10" required />
                    @elseif ($resource === 'academic-years')
                        <x-input name="starts_on" :label="__('Tarehe ya kuanza')" type="date" required />
                        <x-input name="ends_on" :label="__('Tarehe ya kumaliza')" type="date" required />
                        <label class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-body"><input type="checkbox" name="is_current" value="1" class="rounded border-border text-primary">{{ __('Mwaka wa sasa') }}</label>
                    @elseif ($resource === 'class-groups')
                        <label class="text-sm font-semibold text-body">{{ __('Kozi') }}
                            <select name="course_id" required class="mt-1 min-h-11 w-full rounded-xl border-border">
                                @foreach ($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach
                            </select>
                        </label>
                        <label class="text-sm font-semibold text-body">{{ __('Mwaka wa masomo') }}
                            <select name="academic_year_id" required class="mt-1 min-h-11 w-full rounded-xl border-border">
                                @foreach ($years as $year)<option value="{{ $year->id }}">{{ $year->name }}</option>@endforeach
                            </select>
                        </label>
                        <x-input name="year_of_study" :label="__('Mwaka wa kusoma')" type="number" min="1" max="10" required />
                    @endif
                    <div class="sm:col-span-2"><x-button type="submit">{{ __('Ongeza taarifa') }}</x-button></div>
                </form>

                <div class="mt-5 divide-y divide-border">
                    @forelse ($records[$resource] as $record)
                        <div class="py-4">
                            <form method="POST" action="{{ route('admin.institution.structure.update', ['resource' => $resource, 'record' => $record->id, 'institution_id' => $institution->id]) }}" class="grid gap-3 sm:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <x-input name="name" :label="__('Jina')" :value="$record->name" required />
                                @if (in_array($resource, ['faculties', 'departments', 'courses'], true))
                                    <x-input name="code" :label="__('Msimbo')" :value="$record->code" required />
                                @endif
                                @if ($resource === 'departments')
                                    <select name="faculty_id" required class="min-h-11 rounded-xl border-border">@foreach ($faculties as $faculty)<option value="{{ $faculty->id }}" @selected($record->faculty_id === $faculty->id)>{{ $faculty->name }}</option>@endforeach</select>
                                @elseif ($resource === 'courses')
                                    <select name="department_id" required class="min-h-11 rounded-xl border-border">@foreach ($departments as $department)<option value="{{ $department->id }}" @selected($record->department_id === $department->id)>{{ $department->name }}</option>@endforeach</select>
                                    <x-input name="duration_years" :label="__('Muda wa masomo (miaka)')" type="number" min="1" max="10" :value="$record->duration_years" required />
                                @elseif ($resource === 'academic-years')
                                    <x-input name="starts_on" :label="__('Tarehe ya kuanza')" type="date" :value="$record->starts_on->format('Y-m-d')" required />
                                    <x-input name="ends_on" :label="__('Tarehe ya kumaliza')" type="date" :value="$record->ends_on->format('Y-m-d')" required />
                                    <label class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-body"><input type="checkbox" name="is_current" value="1" @checked($record->is_current) class="rounded border-border text-primary">{{ __('Mwaka wa sasa') }}</label>
                                @elseif ($resource === 'class-groups')
                                    <select name="course_id" required class="min-h-11 rounded-xl border-border">@foreach ($courses as $course)<option value="{{ $course->id }}" @selected($record->course_id === $course->id)>{{ $course->name }}</option>@endforeach</select>
                                    <select name="academic_year_id" required class="min-h-11 rounded-xl border-border">@foreach ($years as $year)<option value="{{ $year->id }}" @selected($record->academic_year_id === $year->id)>{{ $year->name }}</option>@endforeach</select>
                                    <x-input name="year_of_study" :label="__('Mwaka wa kusoma')" type="number" min="1" max="10" :value="$record->year_of_study" required />
                                @endif
                                <div class="flex flex-wrap gap-3 sm:col-span-2">
                                    <x-button type="submit" variant="secondary">{{ __('Hifadhi mabadiliko') }}</x-button>
                            </form>
                                    <form method="POST" action="{{ route('admin.institution.structure.destroy', ['resource' => $resource, 'record' => $record->id, 'institution_id' => $institution->id]) }}" onsubmit="return confirm('{{ __('Una uhakika?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger">{{ __('Futa') }}</x-button>
                                    </form>
                                </div>
                        </div>
                    @empty
                        <x-empty-state :title="__('Hakuna taarifa bado')" :description="__('Ongeza rekodi ya kwanza katika sehemu hii.')" />
                    @endforelse
                </div>
            </x-card>
        @endforeach
    </div>
@endsection
