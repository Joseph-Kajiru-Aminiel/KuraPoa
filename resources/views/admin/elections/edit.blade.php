@extends('layouts.admin')

@section('title', 'Hariri uchaguzi')
@section('page-heading', 'Hariri uchaguzi')

@section('content')
    <x-section-title title="Hariri uchaguzi" description="Taarifa hubadilishwa wakati uchaguzi bado ni rasimu." />
    <x-card class="mt-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.elections.update', $election) }}" class="space-y-5">
            @method('PUT')
            @include('admin.elections._form')
        </form>
    </x-card>
@endsection
