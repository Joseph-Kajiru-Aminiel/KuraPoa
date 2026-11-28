<section>
    <header>
        <h2 class="text-lg font-bold text-ink">Badili nenosiri</h2>
        <p class="mt-1 text-sm text-muted">Tumia nenosiri imara na la kipekee kulinda akaunti yako.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <x-input name="current_password" label="Nenosiri la sasa" type="password" autocomplete="current-password" />
        <x-input name="password" label="Nenosiri jipya" type="password" autocomplete="new-password" />
        <x-input name="password_confirmation" label="Thibitisha nenosiri jipya" type="password" autocomplete="new-password" />

        <div class="flex items-center gap-4">
            <x-button type="submit">Hifadhi</x-button>

            @if (session('status') === 'password-updated')
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
