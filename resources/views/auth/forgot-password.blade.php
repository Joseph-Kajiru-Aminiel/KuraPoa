<x-guest-layout>
    @section('title', __('Umesahau nenosiri').' | KuraPoa')

    <x-auth-card
        :status="__('Salama')"
        illustration="recovery"
        :title="__('Umesahau nenosiri?')"
        :description="__('Weka barua pepe ya akaunti yako ili tutume kiungo cha kuweka nenosiri jipya.')"
    >
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4" x-data="formSubmitState()" @submit="submitOnce($event)">
            @csrf

            <x-form-field name="email" :label="__('Barua pepe')" type="email" icon="email" :value="old('email')" required autofocus autocomplete="email" />

            <div class="flex min-h-7 items-center text-sm" aria-live="polite">
                @if (session('status'))
                    <span class="font-medium text-forest" role="status">{{ session('status') }}</span>
                @elseif ($errors->any())
                    <span class="text-danger" role="alert">{{ $errors->first() }}</span>
                @endif
            </div>

            <x-primary-button :loading-label="__('Inatuma...')">{{ __('Tuma kiungo') }}</x-primary-button>
        </form>

        <x-slot:help>
            <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center px-2 font-semibold text-forest hover:underline focus-visible:ring-forest">{{ __('Rudi kuingia') }}</a>
        </x-slot:help>

        <x-slot:note>
            {{ __('Utaweza kuweka nenosiri jipya kupitia kiungo cha barua pepe kitakachotumwa kwako.') }}
        </x-slot:note>
    </x-auth-card>
</x-guest-layout>
