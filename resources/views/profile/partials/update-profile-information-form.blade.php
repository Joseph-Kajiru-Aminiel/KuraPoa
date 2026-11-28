<section>
    <header>
        <h2 class="text-lg font-bold text-ink">Taarifa za akaunti</h2>
        <p class="mt-1 text-sm text-muted">Sasisha jina na anwani ya barua pepe ya akaunti yako.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <x-input name="name" label="Jina" :value="old('name', $user->name)" required autofocus autocomplete="name" />
        <x-input name="email" label="Barua pepe" type="email" :value="old('email', $user->email)" required autocomplete="username" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm text-body">
                        Barua pepe yako haijathibitishwa.
                        <button form="send-verification" class="font-semibold text-primary underline-offset-4 hover:underline focus-visible:ring-primary">
                            Tuma kiungo cha uthibitisho tena.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <x-alert type="success" class="mt-3">Kiungo kipya kimetumwa kwenye barua pepe yako.</x-alert>
                    @endif
                </div>
            @endif

        <div class="flex items-center gap-4">
            <x-button type="submit">Hifadhi</x-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-muted"
                >Imehifadhiwa.</p>
            @endif
        </div>
    </form>
</section>
