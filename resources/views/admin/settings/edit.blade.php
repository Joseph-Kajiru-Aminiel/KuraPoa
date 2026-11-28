@extends('layouts.admin')

@section('title', __('Mipangilio'))
@section('page-heading', __('Mipangilio'))

@section('content')
    <x-section-title :title="__('Mipangilio')" :description="__('Weka viungo vya mitandao ya kijamii na mawasiliano ya KuraPoa.')" />
    <x-card class="mt-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')
            <x-input name="youtube_url" :label="__('Kiungo cha YouTube')" type="url" :value="$settings['youtube_url'] ?? ''" placeholder="https://youtube.com/..." />
            <x-input name="instagram_url" :label="__('Kiungo cha Instagram')" type="url" :value="$settings['instagram_url'] ?? ''" placeholder="https://instagram.com/..." />
            <x-input name="whatsapp_number" :label="__('Namba ya WhatsApp')" :value="$settings['whatsapp_number'] ?? ''" placeholder="2557XXXXXXXX" hint="{{ __('Weka tarakimu pekee, ikianza na 255.') }}" />
            <x-input name="contact_email" :label="__('Barua pepe ya mawasiliano')" type="email" :value="$settings['contact_email'] ?? ''" />
            <x-button type="submit">{{ __('Hifadhi mipangilio') }}</x-button>
        </form>
    </x-card>
@endsection
