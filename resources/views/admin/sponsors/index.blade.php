@extends('layouts.admin')

@section('title', __('Washirika Wetu'))
@section('page-heading', __('Washirika Wetu'))

@section('content')
    <x-section-title :title="__('Washirika Wetu')" :description="__('Simamia washirika wanaoonekana kwenye ukurasa wa mwanzo.')" />
    <x-card class="mt-6">
        <h2 class="text-lg font-bold text-ink">{{ __('Ongeza mshirika') }}</h2>
        <form method="POST" action="{{ route('admin.sponsors.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <x-input name="name" :label="__('Jina')" required />
            <x-input name="url" :label="__('Tovuti')" type="url" />
            <div x-data="{ preview: null, previewFile(event) { const file = event.target.files[0]; this.preview = file ? URL.createObjectURL(file) : null } }">
                <label for="new-sponsor-logo" class="mb-2 block text-sm font-semibold text-ink">{{ __('Nembo') }}</label>
                <div class="mb-2 flex h-24 items-center justify-center rounded-xl bg-primary-light p-3">
                    <img x-show="preview" x-cloak :src="preview" alt="{{ __('Onyesho la nembo mpya') }}" class="h-full max-w-full object-contain">
                    <span x-show="!preview" class="text-sm text-muted">{{ __('Hakuna nembo iliyochaguliwa') }}</span>
                </div>
                <input id="new-sponsor-logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml" @change="previewFile($event)" class="min-h-11 w-full rounded-xl border border-border bg-card px-3 py-2 text-sm text-body focus:border-primary focus:ring-primary">
                <p class="mt-1 text-xs text-muted">{{ __('PNG, JPG, WEBP au SVG; hadi MB 2.') }}</p>
            </div>
            <x-input name="sort_order" :label="__('Mpangilio')" type="number" value="0" required />
            <label class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-body">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-border text-primary focus:ring-primary">
                {{ __('Inatumika') }}
            </label>
            <div class="sm:col-span-2"><x-button type="submit">{{ __('Hifadhi mshirika') }}</x-button></div>
        </form>
    </x-card>

    <div class="mt-6 space-y-4">
        @forelse ($sponsors as $sponsor)
            <x-card>
                <form method="POST" action="{{ route('admin.sponsors.update', $sponsor) }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <x-input name="name" :label="__('Jina')" :value="$sponsor->name" required />
                    <x-input name="url" :label="__('Tovuti')" type="url" :value="$sponsor->url" />
                    <div x-data="{ preview: '{{ $sponsor->logo ? asset('storage/'.$sponsor->logo) : '' }}', previewFile(event) { const file = event.target.files[0]; this.preview = file ? URL.createObjectURL(file) : this.preview } }">
                        <label for="sponsor-logo-{{ $sponsor->id }}" class="mb-2 block text-sm font-semibold text-ink">{{ __('Badilisha nembo') }}</label>
                        <div class="mb-2 flex h-24 items-center justify-center rounded-xl bg-primary-light p-3">
                            <img x-show="preview" :src="preview" alt="{{ __('Onyesho la nembo') }}" class="h-full max-w-full object-contain">
                            <span x-show="!preview" class="text-sm text-muted">{{ __('Hakuna nembo iliyochaguliwa') }}</span>
                        </div>
                        <input id="sponsor-logo-{{ $sponsor->id }}" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml" @change="previewFile($event)" class="min-h-11 w-full rounded-xl border border-border bg-card px-3 py-2 text-sm text-body focus:border-primary focus:ring-primary">
                        <p class="mt-1 text-xs text-muted">{{ __('PNG, JPG, WEBP au SVG; hadi MB 2.') }}</p>
                    </div>
                    <x-input name="sort_order" :label="__('Mpangilio')" type="number" :value="$sponsor->sort_order" required />
                    <label class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-body">
                        <input type="checkbox" name="is_active" value="1" @checked($sponsor->is_active) class="rounded border-border text-primary focus:ring-primary">
                        {{ __('Inatumika') }}
                    </label>
                    <div class="flex flex-wrap gap-3 sm:col-span-2">
                        <x-button type="submit" variant="secondary">{{ __('Hifadhi mabadiliko') }}</x-button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.sponsors.destroy', $sponsor) }}" onsubmit="return confirm('{{ __('Una uhakika unataka kumfuta mshirika huyu?') }}')">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">{{ __('Futa') }}</x-button>
                </form>
            </x-card>
        @empty
            <x-empty-state :title="__('Bado hakuna washirika')" :description="__('Washirika unaowaongeza wataonekana hapa.')" />
        @endforelse
    </div>
@endsection
