<x-guest-layout>
    @section('title', __('Weka nenosiri jipya').' | KuraPoa')

    <x-auth-card
        :status="__('Salama')"
        illustration="recovery"
        :title="__('Weka nenosiri jipya')"
        :description="__('Chagua nenosiri jipya la akaunti yako.')"
    >
        <form method="POST" action="{{ route('password.store') }}" class="space-y-4" x-data="formSubmitState()" @submit="submitOnce($event)">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <x-form-field name="email" :label="__('Barua pepe')" type="email" icon="email" :value="old('email', $request->email)" required autocomplete="username" />
            <x-form-field name="password" :label="__('Nenosiri jipya')" type="password" icon="password" required autocomplete="new-password" />
            <x-form-field name="password_confirmation" :label="__('Thibitisha nenosiri jipya')" type="password" icon="password" required autocomplete="new-password" />

            <div class="flex min-h-7 items-center text-sm text-danger" aria-live="polite">
                @if ($errors->any())
                    <span role="alert">{{ $errors->first() }}</span>
                @endif
            </div>

            <x-primary-button :loading-label="__('Inahifadhi...')">{{ __('Hifadhi nenosiri') }}</x-primary-button>
        </form>

        <x-slot:note>
            {{ __('Kiungo cha kuweka nenosiri jipya ni cha matumizi binafsi.') }}
        </x-slot:note>
    </x-auth-card>
</x-guest-layout>
