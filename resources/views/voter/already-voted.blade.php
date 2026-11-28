@extends('layouts.voter-auth')

@section('title', __('Tayari umeshapiga kura'))

@section('content')
    <x-auth-card
        status-variant="success"
        illustration="verified"
        :status="__('Tayari umeshapiga kura')"
        :title="__('Umeshapiga kura')"
        :description="__('Tayari umeshiriki katika uchaguzi huu. Asante kwa kutumia haki yako ya kupiga kura.')"
    >
        @if ($election)
            <p class="mb-4 text-center text-sm font-bold text-forest">{{ $election->title }}</p>
        @endif

        <x-button :href="route('home')" class="w-full">{{ __('Rudi mwanzo') }}</x-button>

        <x-slot:note>
            {{ __('Asante kwa kushiriki katika uchaguzi wa wanafunzi.') }}
        </x-slot:note>
    </x-auth-card>
@endsection
