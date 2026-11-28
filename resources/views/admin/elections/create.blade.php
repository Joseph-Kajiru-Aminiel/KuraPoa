@extends('layouts.admin')

@section('title', 'Unda uchaguzi')
@section('page-heading', 'Unda uchaguzi')

@section('content')
    <x-section-title title="Unda uchaguzi mpya" description="Weka jina na muda wa uchaguzi. Utaanza kama rasimu." />
    <x-card class="mt-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.elections.store') }}" class="space-y-5">
            @include('admin.elections._form')
        </form>
    </x-card>
@endsection
