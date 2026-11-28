<x-guest-layout>
    @section('title', __('Thibitisha nenosiri').' | KuraPoa')

    <x-auth-card
        :status="__('Salama')"
        illustration="verified"
        :title="__('Thibitisha nenosiri')"
        :description="__('Eneo hili ni salama. Thibitisha nenosiri lako ili kuendelea.')"
    >
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4" x-data="formSubmitState()" @submit="submitOnce($event)">
            @csrf

            <x-form-field name="password" :label="__('Nenosiri')" type="password" icon="password" required autocomplete="current-password" />
            <div class="flex min-h-7 items-center text-sm text-danger" aria-live="polite">
                @if ($errors->any())
                    <span role="alert">{{ $errors->first() }}</span>
                @endif
            </div>
            <x-primary-button :loading-label="__('Inathibitisha...')">{{ __('Thibitisha') }}</x-primary-button>
        </form>
    </x-auth-card>
</x-guest-layout>
