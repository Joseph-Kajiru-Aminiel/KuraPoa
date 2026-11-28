<x-guest-layout>
    @section('title', __('Ingia').' | KuraPoa')

    <x-auth-card
        :status="__('Salama')"
        illustration="admin"
        :title="__('Karibu tena')"
        :description="__('Ingia kwenye akaunti ya msimamizi wa KuraPoa.')"
    >
        <p class="sr-only">{{ __('Sehemu ya usimamizi') }}</p>

        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="formSubmitState()" @submit="submitOnce($event)">
            @csrf

            <x-form-field name="email" :label="__('Barua pepe')" type="email" icon="email" :value="old('email')" required autocomplete="username" autofocus />
            <x-form-field name="password" :label="__('Nenosiri')" type="password" icon="password" required autocomplete="current-password" />

            <label for="remember" class="flex min-h-11 cursor-pointer items-center gap-3 text-sm font-medium text-muted-ink">
                <input id="remember" type="checkbox" name="remember" @checked(old('remember')) class="h-4 w-4 rounded border-field-border text-forest focus:ring-forest">
                {{ __('Nikumbuke') }}
            </label>

            <div class="flex min-h-7 items-center text-sm text-danger" aria-live="polite">
                @if (session('status'))
                    <span class="font-medium text-forest" role="status">{{ session('status') }}</span>
                @elseif ($errors->any())
                    <span role="alert">{{ $errors->first() }}</span>
                @endif
            </div>

            <x-primary-button :loading-label="__('Inaingia...')">{{ __('Ingia') }}</x-primary-button>
        </form>

        @if (Route::has('password.request'))
            <x-slot:help>
                <a class="inline-flex min-h-11 items-center justify-center px-2 font-semibold text-forest underline-offset-4 hover:underline focus-visible:ring-forest" href="{{ route('password.request') }}">
                    {{ __('Umesahau nenosiri?') }}
                </a>
            </x-slot:help>
        @endif

        <x-slot:note>
            <span class="inline-flex items-center justify-center gap-2">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5 8V6a5 5 0 0 1 10 0v2h.5A1.5 1.5 0 0 1 17 9.5v7a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 16.5v-7A1.5 1.5 0 0 1 4.5 8H5Zm2 0h6V6a3 3 0 1 0-6 0v2Z" clip-rule="evenodd"/></svg>
                {{ __('Ufikiaji huu ni kwa wasimamizi walioidhinishwa tu.') }}
            </span>
        </x-slot:note>
    </x-auth-card>
</x-guest-layout>
