<x-guest-layout>
    @section('title', __('Thibitisha barua pepe').' | KuraPoa')

    <x-auth-card
        :status="__('Salama')"
        illustration="verified"
        :title="__('Thibitisha barua pepe')"
        :description="__('Thibitisha anwani yako kwa kutumia kiungo tulichokutumia. Unaweza kuomba kiungo kingine ukikikosa.')"
    >
        @if (session('status') === 'verification-link-sent')
            <x-alert type="success" class="mb-4">{{ __('Kiungo kipya kimetumwa kwenye barua pepe yako.') }}</x-alert>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="space-y-4" x-data="formSubmitState()" @submit="submitOnce($event)">
            @csrf
            <x-primary-button :loading-label="__('Inatuma...')">{{ __('Tuma kiungo tena') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <x-button type="submit" variant="secondary" class="w-full">{{ __('Toka kwenye akaunti') }}</x-button>
        </form>

        <x-slot:note>
            {{ __('Tumia kiungo kilichotumwa kwenye barua pepe yako ili kuthibitisha akaunti.') }}
        </x-slot:note>
    </x-auth-card>
</x-guest-layout>
