@extends('layouts.admin')

@section('title', __('Taasisi'))
@section('page-heading', __('Taasisi'))

@section('content')
    <x-section-title :title="$institution->name" :description="__('Hariri taarifa za taasisi yako.')" />
    <x-card class="mt-6">
        <form method="POST" action="{{ route('admin.institution.update', request()->only('institution_id')) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <x-input name="name" :label="__('Jina la taasisi')" :value="$institution->name" required />
            <x-input name="code" :label="__('Msimbo wa taasisi')" :value="$institution->code" required />
            <x-input name="contact_phone" :label="__('Simu ya mawasiliano')" :value="$institution->contact_phone" />
            <x-input name="contact_email" :label="__('Barua pepe ya mawasiliano')" type="email" :value="$institution->contact_email" />
            <x-input name="reg_number_pattern" :label="__('Muundo wa namba za usajili (regex)')" :value="$institution->reg_number_pattern" :hint="__('Acha wazi ikiwa hakuna muundo maalum.')" />
            <x-input name="logo" :label="__('Nembo ya taasisi')" type="file" accept="image/*" />
            @if ($institution->logo)
                <img src="{{ asset('storage/'.$institution->logo) }}" alt="{{ __('Nembo ya taasisi') }}" class="h-16 w-16 rounded-xl object-contain">
            @endif
            <x-button type="submit">{{ __('Hifadhi taarifa') }}</x-button>
        </form>
        <a href="{{ route('admin.institution.structure', request()->only('institution_id')) }}" class="mt-6 inline-flex min-h-11 items-center font-semibold text-primary">{{ __('Simamia vitivo, idara, kozi na madarasa') }}</a>
    </x-card>
@endsection
